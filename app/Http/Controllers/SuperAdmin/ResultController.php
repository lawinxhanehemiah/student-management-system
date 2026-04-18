<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\AcademicYear;
use App\Models\Module;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MinistryResultsImport;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index(Request $request)
    {
        $query = Result::with(['student.user', 'module', 'academicYear', 'gradingSystem'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('academic_year')) {
            $query->where('academic_year_id', $request->academic_year);
        }
        if ($request->filled('module')) {
            $query->where('module_id', $request->module);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->paginate(20);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $modules = Module::orderBy('code')->get();

        return view('superadmin.results.index', compact('results', 'academicYears', 'modules'));
    }

    public function show(Result $result)
    {
        return view('superadmin.results.show', compact('result'));
    }

    public function destroy(Result $result)
    {
        if ($result->status === 'published') {
            return back()->with('error', 'Cannot delete published results.');
        }
        $result->delete();
        return back()->with('success', 'Result deleted.');
    }

    public function importMinistryForm()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('superadmin.results.import-ministry', compact('academicYears'));
    }

    public function importMinistry(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|in:1,2',
        ]);

        $batchId = uniqid('ministry_', true);
        Excel::import(new MinistryResultsImport($request->academic_year_id, $request->semester, $batchId, $this->resultService), $request->file('file'));

        return redirect()->route('superadmin.results.index')->with('success', 'Ministry results imported successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'exists:results,id',
            'status' => 'nullable|in:draft,pending_hod,pending_academic,pending_principal,published,rejected',
        ]);

        foreach ($request->result_ids as $id) {
            $result = Result::find($id);
            if ($result->status !== 'published') {
                $result->status = $request->status;
                $result->save();
            }
        }

        return back()->with('success', 'Bulk update completed.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'exists:results,id',
        ]);

        foreach ($request->result_ids as $id) {
            $result = Result::find($id);
            if ($result->status !== 'published') {
                $result->delete();
            }
        }

        return back()->with('success', 'Bulk delete completed.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'exists:results,id',
        ]);

        foreach ($request->result_ids as $id) {
            $result = Result::find($id);
            if (in_array($result->status, ['pending_hod', 'pending_academic', 'pending_principal'])) {
                $this->resultService->approve($result);
            }
        }

        return back()->with('success', 'Bulk approval completed.');
    }

    public function integrityReport()
    {
        $missingResults = $this->getMissingResults();
        $stuckWorkflows = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
            ->where('updated_at', '<', now()->subDays(5))
            ->count();
        $orphanedVersions = $this->getOrphanedVersionsCount();

        return view('superadmin.results.integrity-report', compact('missingResults', 'stuckWorkflows', 'orphanedVersions'));
    }

    public function integrityFix(Request $request)
    {
        $action = $request->action;

        if ($action === 'fix-missing') {
            $this->fixMissingResults();
        } elseif ($action === 'fix-stuck') {
            $this->fixStuckWorkflows();
        } elseif ($action === 'fix-orphaned') {
            $this->fixOrphanedVersions();
        } else {
            return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', 'Integrity fix applied.');
    }

    public function missingResults()
    {
        $missing = $this->getMissingResults();
        return view('superadmin.results.missing-results', compact('missing'));
    }

    public function stuckWorkflows()
    {
        $stuck = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
            ->where('updated_at', '<', now()->subDays(5))
            ->with(['student.user', 'module', 'academicYear'])
            ->get();

        return view('superadmin.results.stuck-workflows', compact('stuck'));
    }

    public function export(Request $request)
    {
        $query = Result::with(['student', 'module', 'academicYear']);

        if ($request->filled('academic_year')) {
            $query->where('academic_year_id', $request->academic_year);
        }
        if ($request->filled('module')) {
            $query->where('module_id', $request->module);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->get();

        $fileName = 'results_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Registration Number', 'Student Name', 'Module Code', 'Module Name', 'CA Score', 'Exam Score', 'Final Score', 'Grade', 'Grade Point', 'Status', 'Source']);

            foreach ($results as $result) {
                fputcsv($file, [
                    $result->student->registration_number,
                    $result->student->user->full_name ?? '',
                    $result->module->code,
                    $result->module->name,
                    $result->ca_score,
                    $result->exam_score,
                    $result->final_score ?? ($result->ca_score && $result->exam_score ? ($result->ca_score * 0.4 + $result->exam_score * 0.6) : ''),
                    $result->grade,
                    $result->grade_point,
                    $result->status,
                    $result->source,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Private helpers
    private function getMissingResults()
    {
        // Example: Students registered for a module but have no result for that semester
        return \DB::table('course_registrations')
            ->leftJoin('results', function ($join) {
                $join->on('course_registrations.student_id', '=', 'results.student_id')
                    ->on('course_registrations.module_id', '=', 'results.module_id')
                    ->on('course_registrations.academic_year_id', '=', 'results.academic_year_id')
                    ->on('course_registrations.semester', '=', 'results.semester')
                    ->where('results.is_current', true);
            })
            ->whereNull('results.id')
            ->count();
    }

    private function getOrphanedVersionsCount()
    {
        // Count groups where more than one record has is_current = 1
        return \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    private function fixMissingResults()
    {
        // Placeholder: actual fix would involve creating missing results from registrations
        // For now, just log or notify
        \Log::info('Integrity fix: missing results - manual action required.');
    }

    private function fixStuckWorkflows()
    {
        // Auto-approve stuck results after 5 days? Or notify?
        $stuck = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
            ->where('updated_at', '<', now()->subDays(5))
            ->get();

        foreach ($stuck as $result) {
            // For example, auto-approve to next stage
            if ($result->status === 'pending_hod') {
                $this->resultService->approve($result, 'Auto-approved by system after timeout');
            } elseif ($result->status === 'pending_academic') {
                $this->resultService->forwardToPrincipal($result);
            } elseif ($result->status === 'pending_principal') {
                $this->resultService->publish($result);
            }
        }
    }

    private function fixOrphanedVersions()
    {
        // Ensure only one version per group is marked current
        $groups = \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $latest = Result::where($group->toArray())
                ->where('is_current', true)
                ->orderBy('version', 'desc')
                ->first();

            Result::where($group->toArray())
                ->where('is_current', true)
                ->where('id', '!=', $latest->id)
                ->update(['is_current' => false]);
        }
    }
}