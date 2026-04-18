<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Student;
use App\Models\GradingSystem;
use Illuminate\Http\Request;

class IntegrityController extends Controller
{
    public function dashboard()
    {
        $summary = [
            'missing_results' => $this->countMissingResults(),
            'stuck_workflows' => Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
                ->where('updated_at', '<', now()->subDays(5))
                ->count(),
            'orphaned_versions' => $this->countOrphanedVersions(),
            'grade_mismatch' => $this->countGradeMismatch(),
            'duplicate_current' => $this->countDuplicateCurrent(),
        ];

        return view('superadmin.integrity.dashboard', compact('summary'));
    }

    public function runChecks(Request $request)
    {
        $type = $request->type;
        $results = [];

        if ($type === 'missing' || !$type) {
            $results['missing'] = $this->getMissingResults();
        }
        if ($type === 'stuck' || !$type) {
            $results['stuck'] = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
                ->where('updated_at', '<', now()->subDays(5))
                ->with(['student.user', 'module', 'academicYear'])
                ->get();
        }
        if ($type === 'orphaned' || !$type) {
            $results['orphaned'] = $this->getOrphanedVersions();
        }
        if ($type === 'grade_mismatch' || !$type) {
            $results['grade_mismatch'] = $this->getGradeMismatches();
        }

        return view('superadmin.integrity.checks', compact('results', 'type'));
    }

    public function repair(Request $request)
    {
        $action = $request->action;

        if ($action === 'fix-missing') {
            $this->fixMissingResults();
        } elseif ($action === 'fix-stuck') {
            $this->fixStuckWorkflows();
        } elseif ($action === 'fix-orphaned') {
            $this->fixOrphanedVersions();
        } elseif ($action === 'fix-grade-mismatch') {
            $this->fixGradeMismatches();
        } else {
            return back()->with('error', 'Invalid repair action.');
        }

        return back()->with('success', 'Repair completed.');
    }

    public function logs()
    {
        return view('superadmin.integrity.logs');
    }

    // ---------------- Private Helpers ----------------

    private function countMissingResults()
    {
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

    private function getMissingResults()
    {
        return \DB::table('course_registrations')
            ->leftJoin('results', function ($join) {
                $join->on('course_registrations.student_id', '=', 'results.student_id')
                    ->on('course_registrations.module_id', '=', 'results.module_id')
                    ->on('course_registrations.academic_year_id', '=', 'results.academic_year_id')
                    ->on('course_registrations.semester', '=', 'results.semester')
                    ->where('results.is_current', true);
            })
            ->whereNull('results.id')
            ->select('course_registrations.*')
            ->get();
    }

    private function countOrphanedVersions()
    {
        return \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    private function getOrphanedVersions()
    {
        $groups = \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $results = collect();
        foreach ($groups as $group) {
            $items = Result::where((array) $group)->where('is_current', true)->get();
            $results = $results->merge($items);
        }
        return $results;
    }

    private function countGradeMismatch()
    {
        return Result::whereNotNull('grading_system_id')
            ->whereRaw('grade != (SELECT grade FROM grading_systems WHERE grading_systems.id = results.grading_system_id AND (results.ca_score * 0.4 + results.exam_score * 0.6) BETWEEN grading_systems.min_score AND grading_systems.max_score)')
            ->count();
    }

    private function getGradeMismatches()
    {
        return Result::whereNotNull('grading_system_id')
            ->whereRaw('grade != (SELECT grade FROM grading_systems WHERE grading_systems.id = results.grading_system_id AND (results.ca_score * 0.4 + results.exam_score * 0.6) BETWEEN grading_systems.min_score AND grading_systems.max_score)')
            ->with(['student.user', 'module', 'gradingSystem'])
            ->get();
    }

    private function countDuplicateCurrent()
    {
        return \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    private function fixMissingResults()
    {
        $missing = $this->getMissingResults();
        foreach ($missing as $missingItem) {
            Result::create([
                'student_id' => $missingItem->student_id,
                'module_id' => $missingItem->module_id,
                'academic_year_id' => $missingItem->academic_year_id,
                'semester' => $missingItem->semester,
                'status' => 'draft',
                'source' => 'internal',
                'version' => 1,
                'is_current' => true,
            ]);
        }
    }

    private function fixStuckWorkflows()
    {
        $stuck = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
            ->where('updated_at', '<', now()->subDays(5))
            ->get();

        foreach ($stuck as $result) {
            if ($result->status === 'pending_hod') {
                $result->status = 'pending_academic';
            } elseif ($result->status === 'pending_academic') {
                $result->status = 'pending_principal';
            } elseif ($result->status === 'pending_principal') {
                $result->status = 'published';
            }
            $result->save();
        }
    }

    private function fixOrphanedVersions()
    {
        $groups = \DB::table('results')
            ->select('student_id', 'module_id', 'academic_year_id', 'semester')
            ->where('is_current', true)
            ->groupBy('student_id', 'module_id', 'academic_year_id', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $latest = Result::where((array) $group)
                ->where('is_current', true)
                ->orderBy('version', 'desc')
                ->first();

            Result::where((array) $group)
                ->where('is_current', true)
                ->where('id', '!=', $latest->id)
                ->update(['is_current' => false]);
        }
    }

    private function fixGradeMismatches()
    {
        $mismatches = $this->getGradeMismatches();
        foreach ($mismatches as $result) {
            $final = $result->ca_score * 0.4 + $result->exam_score * 0.6;
            $grading = GradingSystem::where('academic_year_id', $result->academic_year_id)
                ->where('min_score', '<=', $final)
                ->where('max_score', '>=', $final)
                ->first();

            if ($grading) {
                $result->grade = $grading->grade;
                $result->grade_point = $grading->grade_point;
                $result->grading_system_id = $grading->id;
                $result->save();
            }
        }
    }
}