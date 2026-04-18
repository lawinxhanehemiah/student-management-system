<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index()
    {
        $results = Result::where('submitted_by', Auth::id())
            ->with(['module', 'student.user', 'academicYear'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('lecturer.results.index', compact('results'));
    }

    public function create(Module $module, $academicYearId, $semester)
    {
        $students = $module->students()->wherePivot('academic_year_id', $academicYearId)
            ->wherePivot('semester', $semester)
            ->with('user')
            ->get();

        return view('lecturer.results.create', compact('module', 'students', 'academicYearId', 'semester'));
    }

    public function store(Request $request, Module $module, $academicYearId, $semester)
    {
        $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
            'ca_scores' => 'nullable|array',
            'exam_scores' => 'nullable|array',
        ]);

        foreach ($request->scores as $studentId => $score) {
            $data = [
                'student_id' => $studentId,
                'module_id' => $module->id,
                'academic_year_id' => $academicYearId,
                'semester' => $semester,
                'ca_score' => $request->ca_scores[$studentId] ?? null,
                'exam_score' => $request->exam_scores[$studentId] ?? null,
                'status' => 'draft',
                'source' => 'internal',
            ];

            $this->resultService->createResult($data);
        }

        // Changed from 'lecturer.results.index' to 'tutor.results.index'
        return redirect()->route('tutor.results.index')
            ->with('success', 'Results saved successfully.');
    }

    public function submitToHod(Result $result)
    {
        $this->resultService->submitToHod($result);
        return redirect()->back()->with('success', 'Result submitted to HOD.');
    }

    public function lock(Result $result)
    {
        $this->resultService->lock($result);
        return redirect()->back()->with('success', 'Result locked for editing.');
    }

    public function unlock(Result $result)
    {
        $this->resultService->unlock($result);
        return redirect()->back()->with('success', 'Result unlocked.');
    }

    // Additional methods for the new pages
    public function consolidated()
    {
        // Fetch consolidated marks for tutor's modules
        $tutor = Auth::user();
        $results = Result::whereHas('module', function($q) use ($tutor) {
            $q->where('tutor_id', $tutor->id);
        })->with(['module', 'student.user', 'academicYear'])->get();

        return view('lecturer.results.consolidated', compact('results'));
    }

    public function approvalStatus()
    {
        $tutor = Auth::user();
        $results = Result::whereHas('module', function($q) use ($tutor) {
            $q->where('tutor_id', $tutor->id);
        })->with(['module', 'student.user'])->orderBy('status')->get();

        return view('lecturer.results.approval_status', compact('results'));
    }

    public function ministryImportForm()
    {
        return view('lecturer.results.ministry_import');
    }

    public function ministryImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        // Your import logic here
        // ...

        return redirect()->back()->with('success', 'Ministry results imported.');
    }
}