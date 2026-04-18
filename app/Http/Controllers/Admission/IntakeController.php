<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IntakeController extends Controller
{
    /**
     * Display all intakes
     */
    public function index(Request $request)
    {
        $intakes = DB::table('intakes as i')
            ->select('i.*', 'ay.name as academic_year_name')
            ->leftJoin('academic_years as ay', 'i.academic_year_id', '=', 'ay.id')
            ->orderByDesc('i.start_date')
            ->paginate(20);
        
        return view('admission.intakes.index', compact('intakes'));
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        $academicYears = DB::table('academic_years')->orderByDesc('start_date')->get();
        $programmes = DB::table('programmes')->where('is_active', 1)->orderBy('name')->get();
        
        return view('admission.intakes.create', compact('academicYears', 'programmes'));
    }
    
    /**
     * Store intake
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:intakes,code',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'application_deadline' => 'required|date',
            'announcement_date' => 'nullable|date',
            'registration_deadline' => 'nullable|date',
            'status' => 'required|in:upcoming,open,closed,completed',
            'max_applications' => 'nullable|integer|min:1',
            'programme_ids' => 'nullable|array',
            'programme_ids.*' => 'exists:programmes,id',
            'capacities' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $intakeId = DB::table('intakes')->insertGetId([
                'name' => $request->name,
                'code' => $request->code,
                'academic_year_id' => $request->academic_year_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'application_deadline' => $request->application_deadline,
                'announcement_date' => $request->announcement_date,
                'registration_deadline' => $request->registration_deadline,
                'status' => $request->status,
                'max_applications' => $request->max_applications,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Assign programmes to intake
            if ($request->programme_ids) {
                foreach ($request->programme_ids as $index => $programmeId) {
                    DB::table('intake_programmes')->insert([
                        'intake_id' => $intakeId,
                        'programme_id' => $programmeId,
                        'capacity' => $request->capacities[$index] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('admission.intakes.index')
                ->with('success', 'Intake created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create intake: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create intake: ' . $e->getMessage());
        }
    }
    
    /**
     * Show intake details
     */
    public function show($id)
    {
        $intake = DB::table('intakes as i')
            ->select('i.*', 'ay.name as academic_year_name')
            ->leftJoin('academic_years as ay', 'i.academic_year_id', '=', 'ay.id')
            ->where('i.id', $id)
            ->first();
        
        if (!$intake) {
            abort(404, 'Intake not found');
        }
        
        $assignedProgrammes = DB::table('intake_programmes as ip')
            ->select('ip.*', 'p.name', 'p.code')
            ->leftJoin('programmes as p', 'ip.programme_id', '=', 'p.id')
            ->where('ip.intake_id', $id)
            ->get();
        
        $applications = DB::table('applications')
            ->where('intake', $intake->name)
            ->count();
        
        return view('admission.intakes.show', compact('intake', 'assignedProgrammes', 'applications'));
    }
    
    /**
     * Edit intake
     */
    public function edit($id)
    {
        $intake = DB::table('intakes')->where('id', $id)->first();
        if (!$intake) {
            abort(404, 'Intake not found');
        }
        
        $academicYears = DB::table('academic_years')->orderByDesc('start_date')->get();
        $programmes = DB::table('programmes')->where('is_active', 1)->orderBy('name')->get();
        
        $assignedProgrammes = DB::table('intake_programmes')
            ->where('intake_id', $id)
            ->pluck('programme_id')
            ->toArray();
        
        return view('admission.intakes.edit', compact('intake', 'academicYears', 'programmes', 'assignedProgrammes'));
    }
    
    /**
     * Update intake
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'academic_year_id' => 'required|exists:academic_years,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'application_deadline' => 'required|date',
            'announcement_date' => 'nullable|date',
            'registration_deadline' => 'nullable|date',
            'status' => 'required|in:upcoming,open,closed,completed',
            'max_applications' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        try {
            DB::table('intakes')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'academic_year_id' => $request->academic_year_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'application_deadline' => $request->application_deadline,
                    'announcement_date' => $request->announcement_date,
                    'registration_deadline' => $request->registration_deadline,
                    'status' => $request->status,
                    'max_applications' => $request->max_applications,
                    'updated_at' => now(),
                ]);
            
            return redirect()->route('admission.intakes.show', $id)
                ->with('success', 'Intake updated successfully!');
                
        } catch (\Exception $e) {
            Log::error('Failed to update intake: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update intake: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete intake
     */
    public function destroy($id)
    {
        try {
            // Check if there are applications for this intake
            $intake = DB::table('intakes')->where('id', $id)->first();
            $applications = DB::table('applications')->where('intake', $intake->name)->count();
            
            if ($applications > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete intake because it has {$applications} applications associated with it."
                ], 400);
            }
            
            DB::table('intake_programmes')->where('intake_id', $id)->delete();
            DB::table('intakes')->where('id', $id)->delete();
            
            return response()->json(['success' => true, 'message' => 'Intake deleted successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete intake'], 500);
        }
    }
    
    /**
     * Assign programme to intake (AJAX)
     */
    public function assignProgramme(Request $request, $intakeId)
    {
        $validator = Validator::make($request->all(), [
            'programme_id' => 'required|exists:programmes,id',
            'capacity' => 'nullable|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            $exists = DB::table('intake_programmes')
                ->where('intake_id', $intakeId)
                ->where('programme_id', $request->programme_id)
                ->exists();
            
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Programme already assigned to this intake'], 400);
            }
            
            DB::table('intake_programmes')->insert([
                'intake_id' => $intakeId,
                'programme_id' => $request->programme_id,
                'capacity' => $request->capacity ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json(['success' => true, 'message' => 'Programme assigned successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign programme'], 500);
        }
    }
    
    /**
     * Remove programme from intake (AJAX)
     */
    public function removeProgramme($intakeId, $programmeId)
    {
        try {
            DB::table('intake_programmes')
                ->where('intake_id', $intakeId)
                ->where('programme_id', $programmeId)
                ->delete();
            
            return response()->json(['success' => true, 'message' => 'Programme removed successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove programme'], 500);
        }
    }
    
    /**
     * Get active intake for application (AJAX)
     */
    public function getActiveIntake()
    {
        $activeIntake = DB::table('intakes')
            ->where('status', 'open')
            ->where('application_deadline', '>=', now())
            ->first();
        
        return response()->json($activeIntake);
    }
}