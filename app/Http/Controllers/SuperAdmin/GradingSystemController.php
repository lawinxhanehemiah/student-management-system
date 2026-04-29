<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GradingSystem;
use Illuminate\Http\Request;

class GradingSystemController extends Controller
{
    public function index()
    {
        $gradingSystems = GradingSystem::with('academicYear')->orderBy('name')->orderBy('program_category')->orderBy('min_score')->get();
        return view('superadmin.grading-systems.index', compact('gradingSystems'));
    }

    public function create()
    {
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        return view('superadmin.grading-systems.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program_category' => 'required|in:all,health,non_health',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gte:min_score',
            'grade' => 'required|string|max:2',
            'grade_point' => 'required|numeric|min:0|max:12',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        GradingSystem::create($validated);

        return redirect()->route('superadmin.grading-systems.index')
            ->with('success', 'Grading system created successfully.');
    }

    public function edit(GradingSystem $gradingSystem)
    {
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();
        return view('superadmin.grading-systems.edit', compact('gradingSystem', 'academicYears'));
    }

    public function update(Request $request, GradingSystem $gradingSystem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'program_category' => 'required|in:all,health,non_health',
            'min_score' => 'required|integer|min:0',
            'max_score' => 'required|integer|min:0|gte:min_score',
            'grade' => 'required|string|max:2',
            'grade_point' => 'required|numeric|min:0|max:12',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $gradingSystem->update($validated);

        return redirect()->route('superadmin.grading-systems.index')
            ->with('success', 'Grading system updated successfully.');
    }

    public function destroy(GradingSystem $gradingSystem)
    {
        if ($gradingSystem->results()->exists()) {
            return back()->with('error', 'Cannot delete grading system that is used in results.');
        }
        $gradingSystem->delete();
        return back()->with('success', 'Grading system deleted successfully.');
    }

    public function toggleActive(GradingSystem $gradingSystem)
    {
        $gradingSystem->is_active = !$gradingSystem->is_active;
        $gradingSystem->save();
        return back()->with('success', 'Status updated successfully.');
    }
}