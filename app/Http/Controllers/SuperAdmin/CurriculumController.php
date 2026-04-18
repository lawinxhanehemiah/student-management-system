<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\Module;
use App\Models\Curriculum;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
{
    $programmes = Programme::where('status', 'active')->pluck('name', 'id');
    
    $programmeId = $request->programme_id;
    $year = $request->year;
    $semester = $request->semester;
    $academicYear = $request->academic_year ?? date('Y') . '/' . (date('Y')+1);

    $curriculum = collect();
    $modules = collect(); // default empty

    if ($programmeId && $year && $semester && $academicYear) {
        // 1. Chukua programme ili kupata department_id
        $programme = Programme::find($programmeId);
        
        if ($programme && $programme->department_id) {
            // 2. Filter modules: department inayolingana + nta_level = year
            $modules = Module::where('is_active', true)
                ->where('department_id', $programme->department_id)
                ->where('nta_level', $year)  // mwaka 4 -> nta_level 4
                ->orderBy('code')
                ->get();
        } else {
            // Kama programme haina department, unaweza kuacha modules empty au kuonyesha warning
            $modules = collect();
        }

        $curriculum = Curriculum::where('programme_id', $programmeId)
            ->where('year', $year)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->with('module')
            ->get();
    }

    return view('superadmin.curriculum.index', compact(
        'programmes', 'curriculum', 'modules', 'programmeId', 'year', 'semester', 'academicYear'
    ));
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'programme_id' => 'required|exists:programmes,id',
        'module_id' => 'required|exists:modules,id',
        'year' => 'required|integer|min:1|max:6',
        'semester' => 'required|integer|min:1|max:2',
        'academic_year' => 'required|string|max:20',
        'credits' => 'nullable|numeric|min:0',
        'is_required' => 'boolean',
        'status' => 'required|in:active,inactive',
        'grading_type' => 'required|in:marks,cbet',
        'pass_mark' => 'nullable|numeric|min:0|max:100',
    ]);

    $validated['is_required'] = $request->has('is_required');

    // Pata programme na module
    $programme = Programme::findOrFail($validated['programme_id']);
    $module = Module::findOrFail($validated['module_id']);

    // Hakikisha module ina department inayolingana na programme
    if ($module->department_id != $programme->department_id) {
        return back()->withErrors(['module_id' => 'Module hii si ya department ya programme iliyochaguliwa.']);
    }

    // Hakikisha nta_level ya module inalingana na year
    if ($module->nta_level != $validated['year']) {
        return back()->withErrors(['module_id' => 'Module hii haifai kwa mwaka uliochagua (inahitaji NTA Level '.$module->nta_level.').']);
    }

    // Tumia default credits/pass_mark kama haijajazwa
    if (empty($validated['credits'])) {
        $validated['credits'] = $module->default_credits;
    }
    if (empty($validated['pass_mark'])) {
        $validated['pass_mark'] = $module->pass_mark;
    }

    // Angalia kama tayari ipo
    $exists = Curriculum::where('programme_id', $validated['programme_id'])
        ->where('module_id', $validated['module_id'])
        ->where('year', $validated['year'])
        ->where('semester', $validated['semester'])
        ->where('academic_year', $validated['academic_year'])
        ->exists();

    if ($exists) {
        return back()->with('error', 'Module tayari imeshatengwa kwa mwaka huu, semesta, na mwaka wa masomo.');
    }

    Curriculum::create($validated);

    return redirect()->route('superadmin.curriculum.index', [
        'programme_id' => $validated['programme_id'],
        'year' => $validated['year'],
        'semester' => $validated['semester'],
        'academic_year' => $validated['academic_year'],
    ])->with('success', 'Module imetengwa kikamilifu.');
}

    public function destroy($id)
    {
        $assignment = Curriculum::findOrFail($id);
        $assignment->delete();

        return back()->with('success', 'Module removed from curriculum.');
    }
}