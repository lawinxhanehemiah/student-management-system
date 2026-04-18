<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentComponent;
use App\Models\Module;
use Illuminate\Http\Request;

class AssessmentComponentController extends Controller
{
    public function index()
    {
        $components = AssessmentComponent::with('module')->orderBy('module_id')->paginate(20);
        return view('superadmin.assessment-components.index', compact('components'));
    }

    public function create()
    {
        $modules = Module::where('is_active', true)->orderBy('code')->get();
        return view('superadmin.assessment-components.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        AssessmentComponent::create($validated);

        return redirect()->route('superadmin.assessment-components.index')
            ->with('success', 'Component added successfully.');
    }

    public function edit(AssessmentComponent $component)
    {
        $modules = Module::where('is_active', true)->orderBy('code')->get();
        return view('superadmin.assessment-components.edit', compact('component', 'modules'));
    }

    public function update(Request $request, AssessmentComponent $component)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $component->update($validated);

        return redirect()->route('superadmin.assessment-components.index')
            ->with('success', 'Component updated successfully.');
    }

    public function destroy(AssessmentComponent $component)
    {
        if ($component->scores()->exists()) {
            return back()->with('error', 'Cannot delete component that has scores recorded.');
        }
        $component->delete();
        return back()->with('success', 'Component deleted.');
    }

    public function toggleActive(AssessmentComponent $component)
    {
        $component->is_active = !$component->is_active;
        $component->save();
        return back()->with('success', 'Status updated.');
    }

    public function copyFromModule(Request $request)
    {
        $request->validate([
            'source_module_id' => 'required|exists:modules,id',
            'target_module_id' => 'required|exists:modules,id',
        ]);

        $sourceComponents = AssessmentComponent::where('module_id', $request->source_module_id)->get();

        foreach ($sourceComponents as $comp) {
            AssessmentComponent::create([
                'module_id' => $request->target_module_id,
                'name' => $comp->name,
                'weight' => $comp->weight,
                'is_active' => $comp->is_active,
            ]);
        }

        return back()->with('success', 'Components copied successfully.');
    }
}