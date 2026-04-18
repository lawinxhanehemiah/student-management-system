<?php
// app/Http/Controllers/SuperAdmin/RepeatModuleFeeController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\RepeatModuleFee;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepeatModuleFeeController extends Controller
{
    /**
     * Display repeat module fees for a programme
     */
    public function index($programmeId, Request $request)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $query = RepeatModuleFee::where('programme_id', $programme->id)
                ->with('academicYear');
            
            // Filters
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }
            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }
            
            $fees = $query->orderBy('academic_year_id', 'desc')
                ->orderBy('level')
                ->orderBy('semester')
                ->paginate(20);
            
            $levels = range(1, 6);
            $semesters = [1 => 'Semester 1', 2 => 'Semester 2'];
            $academicYears = $this->getActiveAcademicYears();
            
            return view('superadmin.repeat-module-fees.index', 
                compact('programme', 'fees', 'levels', 'semesters', 'academicYears'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show form to create repeat module fee
     */
    public function create($programmeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $levels = range(1, 6);
            $semesters = [1 => 'Semester 1', 2 => 'Semester 2'];
            $academicYears = $this->getActiveAcademicYears();
            
            if ($academicYears->isEmpty()) {
                return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                    ->with('error', 'No active academic years found.');
            }
            
            return view('superadmin.repeat-module-fees.create', 
                compact('programme', 'academicYears', 'levels', 'semesters'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.index')
                ->with('error', 'Programme not found: ' . $e->getMessage());
        }
    }
    
    /**
     * Store repeat module fee
     */
    public function store(Request $request, $programmeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'level' => 'required|integer|min:1|max:6',
                'semester' => 'required|integer|in:1,2',
                'total_fee' => 'required|numeric|min:0'
                // NO is_active validation here
            ]);
            
            // Check if exists
            $exists = RepeatModuleFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('level', $validated['level'])
                ->where('semester', $validated['semester'])
                ->exists();
                
            if ($exists) {
                return back()
                    ->withErrors(['level' => 'Repeat module fee already exists for this programme, academic year, level and semester combination.'])
                    ->withInput();
            }
            
            // Add programme_id and is_active (convert checkbox to 1/0)
            $validated['programme_id'] = $programme->id;
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            
            RepeatModuleFee::create($validated);
            
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                ->with('success', 'Repeat module fee added successfully for ' . $programme->name);
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Edit repeat module fee
     */
    public function edit($programmeId, $feeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = RepeatModuleFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $levels = range(1, 6);
            $semesters = [1 => 'Semester 1', 2 => 'Semester 2'];
            $academicYears = $this->getActiveAcademicYears();
            
            return view('superadmin.repeat-module-fees.edit', 
                compact('programme', 'fee', 'academicYears', 'levels', 'semesters'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programmeId)
                ->with('error', 'Fee not found: ' . $e->getMessage());
        }
    }
    
    /**
     * Update repeat module fee
     */
    public function update(Request $request, $programmeId, $feeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = RepeatModuleFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'level' => 'required|integer|min:1|max:6',
                'semester' => 'required|integer|in:1,2',
                'total_fee' => 'required|numeric|min:0'
                // NO is_active validation here
            ]);
            
            // Check duplicate
            $exists = RepeatModuleFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('level', $validated['level'])
                ->where('semester', $validated['semester'])
                ->where('id', '!=', $fee->id)
                ->exists();
                
            if ($exists) {
                return back()
                    ->withErrors(['level' => 'Fee already exists for this programme, academic year, level and semester combination.'])
                    ->withInput();
            }
            
            // Convert checkbox to 1/0
            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $fee->update($validated);
            
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                ->with('success', 'Repeat module fee updated successfully for ' . $programme->name);
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete repeat module fee
     */
    public function destroy($programmeId, $feeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = RepeatModuleFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $fee->delete();
            
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                ->with('success', 'Repeat module fee deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programmeId)
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Copy repeat module fees
     */
    public function copyFees(Request $request, $programmeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $request->validate([
                'from_academic_year_id' => 'required|exists:academic_years,id',
                'to_academic_year_id' => 'required|exists:academic_years,id|different:from_academic_year_id'
            ]);
            
            $fromFees = RepeatModuleFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $request->from_academic_year_id)
                ->get();
            
            $copiedCount = 0;
            foreach ($fromFees as $fee) {
                $exists = RepeatModuleFee::where('programme_id', $programme->id)
                    ->where('academic_year_id', $request->to_academic_year_id)
                    ->where('level', $fee->level)
                    ->where('semester', $fee->semester)
                    ->exists();
                
                if (!$exists) {
                    $newFee = $fee->replicate();
                    $newFee->academic_year_id = $request->to_academic_year_id;
                    $newFee->is_active = $fee->is_active; // Copy status
                    $newFee->save();
                    $copiedCount++;
                }
            }
            
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                ->with('success', 'Copied ' . $copiedCount . ' repeat module fees.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk Create - Show form
     */
    public function bulkCreate($programmeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $levels = range(1, 6);
            $semesters = [1, 2];
            $academicYears = $this->getActiveAcademicYears();
            
            return view('superadmin.repeat-module-fees.bulk-create', 
                compact('programme', 'academicYears', 'levels', 'semesters'));
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programmeId)
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk Store
     */
    public function bulkStore(Request $request, $programmeId)
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'total_fee' => 'required|numeric|min:0',
                'selections' => 'required|array'
                // NO is_active validation here
            ]);
            
            $createdCount = 0;
            $skippedCount = 0;
            $isActive = $request->has('is_active') ? 1 : 0;
            
            foreach ($validated['selections'] as $level => $semesters) {
                foreach ($semesters as $semester => $value) {
                    $exists = RepeatModuleFee::where('programme_id', $programme->id)
                        ->where('academic_year_id', $validated['academic_year_id'])
                        ->where('level', $level)
                        ->where('semester', $semester)
                        ->exists();
                    
                    if (!$exists) {
                        RepeatModuleFee::create([
                            'programme_id' => $programme->id,
                            'academic_year_id' => $validated['academic_year_id'],
                            'level' => $level,
                            'semester' => $semester,
                            'total_fee' => $validated['total_fee'],
                            'is_active' => $isActive
                        ]);
                        $createdCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
            
            return redirect()->route('superadmin.programmes.repeat-module-fees.index', $programme->id)
                ->with('success', "Created {$createdCount} repeat module fees for {$programme->name}. Skipped {$skippedCount} (already exist).");
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Get fee details via AJAX
     */
    public function getFee($programmeId, $level, $semester)
    {
        try {
            $fee = RepeatModuleFee::where('programme_id', $programmeId)
                ->where('level', $level)
                ->where('semester', $semester)
                ->where('is_active', 1)
                ->first();
            
            return response()->json($fee);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Check if fee exists via AJAX
     */
    public function checkExists(Request $request)
    {
        try {
            $exists = RepeatModuleFee::where('programme_id', $request->programme_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('level', $request->level)
                ->where('semester', $request->semester)
                ->exists();
            
            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get active academic years
     */
    private function getActiveAcademicYears()
    {
        if (!Schema::hasTable('academic_years')) {
            return collect();
        }
        
        try {
            return DB::table('academic_years')
                ->where('status', 'active')
                ->where('is_active', 1)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name', 'start_date', 'end_date']);
        } catch (\Exception $e) {
            return collect();
        }
    }
}