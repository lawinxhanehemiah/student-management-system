<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\ProgrammeFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProgrammeFeeController extends Controller
{
    /**
     * Show all fees for a programme
     */
    public function index($programmeId, Request $request) // BADILISHA: Ondoa Programme $programme
    {
        try {
            // Find programme by ID
            $programme = Programme::find($programmeId);
            
            if (!$programme) {
                return redirect()->route('superadmin.programmes.index')
                    ->with('error', 'Programme not found.');
            }
            
            $query = ProgrammeFee::where('programme_id', $programme->id)
                ->with('academicYear');
            
            // Filter by level
            if ($request->has('level')) {
                $query->where('level', $request->level);
            }
            
            // Filter by status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active == '1');
            }
            
            $fees = $query->orderBy('academic_year_id', 'desc')
                ->orderBy('level', 'asc')
                ->paginate(20);
            
            $levels = range(1, 6);
            
            return view('superadmin.programme-fees.index', 
                compact('programme', 'fees', 'levels'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.index')
                ->with('error', 'Error loading fees: ' . $e->getMessage());
        }
    }

    /**
     * Show form to create new fee for a programme
     */
    public function create($programmeId) // BADILISHA: Ondoa Programme $programme
    {
        try {
            // Find programme by ID
            $programme = Programme::findOrFail($programmeId);
            
            // Get available levels
            $levels = range(1, 6);
            
            // Get active academic years
            $academicYears = $this->getActiveAcademicYears();
            
            // Check if academic years exist
            if ($academicYears->isEmpty()) {
                return redirect()->route('superadmin.programmes.fees.index', $programme->id)
                    ->with('error', 'No active academic years found. Please add academic years in System Configuration.');
            }
            
            return view('superadmin.programme-fees.create', 
                compact('programme', 'academicYears', 'levels'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.index')
                ->with('error', 'Programme not found: ' . $e->getMessage());
        }
    }
    
    /**
     * Store a new fee for a programme
     */
    public function store(Request $request, $programmeId) // BADILISHA: Ondoa Programme $programme
    {
        try {
            // Find programme
            $programme = Programme::findOrFail($programmeId);
            
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'level' => 'required|integer|min:1|max:6',
                'registration_fee' => 'required|numeric|min:0',
                'semester_1_fee' => 'required|numeric|min:0',
                'semester_2_fee' => 'required|numeric|min:0',
                'is_active' => 'boolean'
            ]);
            
            // Check if fee already exists
            $exists = ProgrammeFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('level', $validated['level'])
                ->exists();
                
            if ($exists) {
                return back()
                    ->withErrors(['level' => 'A fee already exists for this academic year and level combination.'])
                    ->withInput();
            }
            
            // Add programme_id to validated data
            $validated['programme_id'] = $programme->id;
            $validated['is_active'] = $request->has('is_active');
            
            // Create the fee
            ProgrammeFee::create($validated);
            
            return redirect()->route('superadmin.programmes.fees.index', $programme->id)
                ->with('success', 'Fee added successfully for ' . $programme->name . ' (Level ' . $validated['level'] . ')');
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.index')
                ->with('error', 'Error creating fee: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit fee
     */
    public function edit($programmeId, $feeId) // BADILISHA: Ondoa Programme $programme
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = ProgrammeFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $levels = range(1, 6);
            $academicYears = $this->getActiveAcademicYears();
            
            return view('superadmin.programme-fees.edit', 
                compact('programme', 'fee', 'academicYears', 'levels'));
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.fees.index', $programmeId)
                ->with('error', 'Fee not found: ' . $e->getMessage());
        }
    }
    
    /**
     * Update fee
     */
    public function update(Request $request, $programmeId, $feeId) // BADILISHA
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = ProgrammeFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $validated = $request->validate([
                'academic_year_id' => 'required|exists:academic_years,id',
                'level' => 'required|integer|min:1|max:6',
                'registration_fee' => 'required|numeric|min:0',
                'semester_1_fee' => 'required|numeric|min:0',
                'semester_2_fee' => 'required|numeric|min:0',
                'is_active' => 'boolean'
            ]);
            
            // Check for duplicate (excluding current)
            $exists = ProgrammeFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('level', $validated['level'])
                ->where('id', '!=', $fee->id)
                ->exists();
                
            if ($exists) {
                return back()
                    ->withErrors(['level' => 'A fee already exists for this academic year and level combination.'])
                    ->withInput();
            }
            
            $validated['is_active'] = $request->has('is_active');
            $fee->update($validated);
            
            return redirect()->route('superadmin.programmes.fees.index', $programme->id)
                ->with('success', 'Fee updated successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating fee: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete fee
     */
    public function destroy($programmeId, $feeId) // BADILISHA
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            $fee = ProgrammeFee::where('programme_id', $programme->id)
                ->findOrFail($feeId);
            
            $fee->delete();
            
            return redirect()->route('superadmin.programmes.fees.index', $programme->id)
                ->with('success', 'Fee deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('superadmin.programmes.fees.index', $programmeId)
                ->with('error', 'Error deleting fee: ' . $e->getMessage());
        }
    }
    
    /**
     * Get active academic years from database
     */
    private function getActiveAcademicYears()
    {
        // Check if academic_years table exists
        if (!Schema::hasTable('academic_years')) {
            return collect();
        }
        
        try {
            return DB::table('academic_years')
                ->where('status', 'active')
                ->where('is_active', 1)
                ->orderBy('start_date', 'desc')
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'start_date', 'end_date']);
                
        } catch (\Exception $e) {
            \Log::error('Error fetching academic years: ' . $e->getMessage());
            return collect();
        }
    }
    
    /**
     * Copy fees from one academic year to another
     */
    public function copyFees(Request $request, $programmeId) // BADILISHA
    {
        try {
            $programme = Programme::findOrFail($programmeId);
            
            $request->validate([
                'from_academic_year_id' => 'required|exists:academic_years,id',
                'to_academic_year_id' => 'required|exists:academic_years,id|different:from_academic_year_id'
            ]);
            
            $fromFees = ProgrammeFee::where('programme_id', $programme->id)
                ->where('academic_year_id', $request->from_academic_year_id)
                ->get();
            
            $copiedCount = 0;
            foreach ($fromFees as $fee) {
                // Check if fee already exists for target year and level
                $exists = ProgrammeFee::where('programme_id', $programme->id)
                    ->where('academic_year_id', $request->to_academic_year_id)
                    ->where('level', $fee->level)
                    ->exists();
                
                if (!$exists) {
                    ProgrammeFee::create([
                        'programme_id' => $programme->id,
                        'academic_year_id' => $request->to_academic_year_id,
                        'level' => $fee->level,
                        'registration_fee' => $fee->registration_fee,
                        'semester_1_fee' => $fee->semester_1_fee,
                        'semester_2_fee' => $fee->semester_2_fee,
                        'is_active' => $fee->is_active
                    ]);
                    $copiedCount++;
                }
            }
            
            return redirect()->route('superadmin.programmes.fees.index', $programme->id)
                ->with('success', 'Copied ' . $copiedCount . ' fees to the new academic year.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error copying fees: ' . $e->getMessage());
        }
    }
    
    /**
     * Global fee settings (for fee-management routes)
     */
    public function globalSettings()
    {
        return view('superadmin.fee-management.settings');
    }
    
    /**
     * Fee transactions (for fee-management routes)
     */
    public function transactions()
    {
        return view('superadmin.fee-management.transactions');
    }
    
    /**
     * Fee reports (for fee-management routes)
     */
    public function reports()
    {
        return view('superadmin.fee-management.reports');
    }
}