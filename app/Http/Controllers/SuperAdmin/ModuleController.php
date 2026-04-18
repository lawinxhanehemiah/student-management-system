<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    /**
     * Display a listing of modules with search & filter
     */
    public function index(Request $request)
    {
        $query = Module::with('department');

        // Search by code or name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        // Filter by status (active/inactive)
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $modules = $query->latest()->paginate(20);

        return view('superadmin.modules.index', compact('modules'));
    }

    /**
     * Show form to create a new module
     */
    public function create()
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        return view('superadmin.modules.create', compact('departments'));
    }

    /**
     * Store a newly created module
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:modules,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id', // Sasa nullable kwa sababu DB imebadilika
            'is_active' => 'boolean',
            'nta_level' => 'required|integer|min:1|max:6',
            'default_credits' => 'required|numeric|min:0|max:99.99',
            'pass_mark' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:Core,Fundamental,Elective',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        Module::create($validated);

        return redirect()->route('superadmin.modules.index')
            ->with('success', 'Module created successfully.');
    }

    /**
     * Show form to edit a module
     */
    public function edit(Module $module)
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        return view('superadmin.modules.edit', compact('module', 'departments'));
    }

    /**
     * Update an existing module
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('modules')->ignore($module->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
            'nta_level' => 'required|integer|min:1|max:6',
            'default_credits' => 'required|numeric|min:0|max:99.99',
            'pass_mark' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:Core,Fundamental,Elective',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['updated_by'] = auth()->id();

        $module->update($validated);

        return redirect()->route('superadmin.modules.index')
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Delete a module (only if not used in any programme)
     */
    public function destroy(Module $module)
    {
        // Check if module is attached to any programme via curriculum
        if ($module->programmes()->exists()) {
            return back()->with('error', 'Cannot delete module assigned to a programme.');
        }
        
        $module->delete();
        
        return redirect()->route('superadmin.modules.index')
            ->with('success', 'Module deleted successfully.');
    }
}