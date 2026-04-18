<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgrammeController extends Controller
{
    public function index(Request $request)
    {
        $query = Programme::query();
        
        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by study mode
        if ($request->has('study_mode')) {
            $query->where('study_mode', $request->study_mode);
        }

        $programmes = $query->latest()->paginate(20);
        
        return view('superadmin.programmes.index', compact('programmes'));
    }

    public function create()
    {
        $studyModes = ['Full Time', 'Part Time', 'Evening', 'Weekend'];
        $statuses = ['active', 'inactive'];
        
        return view('superadmin.programmes.create', compact('studyModes', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:programmes,code',
            'name' => 'required|string|max:255',
            'study_mode' => ['required', Rule::in(['Full Time', 'Part Time', 'Evening', 'Weekend'])],
            'available_seats' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $validated['is_active'] = $request->has('is_active');

        Programme::create($validated);

        return redirect()->route('superadmin.programmes.index')
            ->with('success', 'Programme created successfully.');
    }

    public function edit(Programme $programme)
    {
        $studyModes = ['Full Time', 'Part Time', 'Evening', 'Weekend'];
        $statuses = ['active', 'inactive'];
        
        return view('superadmin.programmes.edit', compact('programme', 'studyModes', 'statuses'));
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('programmes')->ignore($programme->id)],
            'name' => 'required|string|max:255',
            'study_mode' => ['required', Rule::in(['Full Time', 'Part Time', 'Evening', 'Weekend'])],
            'available_seats' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $validated['is_active'] = $request->has('is_active');

        $programme->update($validated);

        return redirect()->route('superadmin.programmes.index')
            ->with('success', 'Programme updated successfully.');
    }

    public function destroy(Programme $programme)
    {
        // Check if programme has fees before deleting
        if ($programme->fees()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete programme with existing fees. Delete fees first.');
        }

        $programme->delete();

        return redirect()->route('superadmin.programmes.index')
            ->with('success', 'Programme deleted successfully.');
    }

    public function toggleStatus(Programme $programme)
    {
        $programme->update([
            'is_active' => !$programme->is_active
        ]);

        return back()->with('success', 'Programme status updated successfully.');
    }
}