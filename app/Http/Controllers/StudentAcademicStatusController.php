<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAcademicStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentAcademicStatusController extends Controller
{
    /**
     * Admission: Initialize student academic status
     * Year 1, Semester 1
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // Hakikisha student hana record active tayari
        $exists = StudentAcademicStatus::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Academic status already exists for this student.');
        }

        StudentAcademicStatus::create([
            'student_id' => $request->student_id,
            'academic_year_id' => $request->academic_year_id,
            'year_of_study' => 1,
            'semester' => 1,
            'status' => 'active',
        ]);

        return back()->with('success', 'Student academic status initialized successfully.');
    }

    /**
     * HOD: Promote student
     */
    public function promote(Request $request, $id)
    {
        $request->validate([
            'next_year' => 'required|integer|min:2|max:6',
            'next_semester' => 'required|integer|in:1,2',
        ]);

        $status = StudentAcademicStatus::findOrFail($id);

        // SECURITY: Only HOD
        if (!Auth::user()->hasRole('HOD')) {
            abort(403, 'Unauthorized action.');
        }

        $status->update([
            'year_of_study' => $request->next_year,
            'semester' => $request->next_semester,
            'status' => 'promoted',
            'promoted_by' => Auth::id(),
            'promoted_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Student promoted successfully.');
    }

    /**
     * View academic history per student
     */
    public function history($studentId)
    {
        $records = StudentAcademicStatus::where('student_id', $studentId)
            ->orderBy('academic_year_id')
            ->get();

        return view('academic.history', compact('records'));
    }
}
