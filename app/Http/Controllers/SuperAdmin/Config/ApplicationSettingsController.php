<?php

namespace App\Http\Controllers\SuperAdmin\Config;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Models\Program;
use App\Models\ProgramAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationSettingsController extends Controller
{
    public function index()
    {
        $settings = ApplicationSetting::with('changedBy')
            ->orderBy('effective_from', 'desc')
            ->paginate(10);
            
        $activeSetting = ApplicationSetting::where('is_active', true)->first();
        
        return view('superadmin.config.application-settings', compact('settings', 'activeSetting'));
    }

    public function create()
    {
        $academicYears = $this->getAcademicYears();
        $programs = Program::where('is_active', true)->get();
        
        return view('superadmin.config.application-settings-create', compact('academicYears', 'programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string',
            'intake' => 'required|in:March,September,Both,Rolling',
            'status' => 'required|in:OPEN,CLOSED,SUSPENDED',
            'opening_date' => 'required|date',
            'closing_date' => 'required|date|after:opening_date',
            
            // Eligibility
            'min_education_level' => 'nullable|string',
            'min_division' => 'nullable|string',
            'min_subjects_pass' => 'nullable|integer',
            'min_grade' => 'nullable|string',
            
            // Fee
            'fee_mode' => 'required|in:FREE,PAID,CONDITIONAL',
            'fee_amount' => 'nullable|numeric|min:0',
            
            // Messages
            'closed_message' => 'nullable|string',
            'eligibility_message' => 'nullable|string',
            'payment_message' => 'nullable|string',
            
            // Programs
            'programs' => 'required|array',
            'programs.*.program_id' => 'required|exists:programs,id',
            'programs.*.is_active' => 'boolean',
            'programs.*.intake_allowed' => 'in:March,September,Both',
            'programs.*.capacity' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $request) {
            // Deactivate previous active setting
            ApplicationSetting::where('is_active', true)->update(['is_active' => false]);
            
            // Create new setting
            $setting = ApplicationSetting::create([
                'academic_year' => $validated['academic_year'],
                'intake' => $validated['intake'],
                'status' => $validated['status'],
                'opening_date' => $validated['opening_date'],
                'closing_date' => $validated['closing_date'],
                'min_education_level' => $validated['min_education_level'],
                'min_division' => $validated['min_division'],
                'min_subjects_pass' => $validated['min_subjects_pass'],
                'min_grade' => $validated['min_grade'],
                'fee_mode' => $validated['fee_mode'],
                'fee_amount' => $validated['fee_amount'] ?? 0,
                'closed_message' => $validated['closed_message'],
                'eligibility_message' => $validated['eligibility_message'],
                'payment_message' => $validated['payment_message'],
                'effective_from' => now(),
                'changed_by' => auth()->id(),
                'is_active' => true,
            ]);
            
            // Create program availabilities
            foreach ($request->programs as $programData) {
                ProgramAvailability::create([
                    'application_setting_id' => $setting->id,
                    'program_id' => $programData['program_id'],
                    'is_active' => $programData['is_active'] ?? true,
                    'intake_allowed' => $programData['intake_allowed'] ?? 'Both',
                    'capacity' => $programData['capacity'] ?? null,
                ]);
            }
        });
        
        return redirect()->route('superadmin.config.application-settings.index')
            ->with('success', 'Application settings created successfully.');
    }

    public function edit($id)
    {
        $setting = ApplicationSetting::with('availablePrograms.program')->findOrFail($id);
        $academicYears = $this->getAcademicYears();
        $programs = Program::where('is_active', true)->get();
        
        return view('superadmin.config.application-settings-edit', compact('setting', 'academicYears', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $setting = ApplicationSetting::findOrFail($id);
        
        $validated = $request->validate([
            'academic_year' => 'required|string',
            'intake' => 'required|in:March,September,Both,Rolling',
            'status' => 'required|in:OPEN,CLOSED,SUSPENDED',
            'opening_date' => 'required|date',
            'closing_date' => 'required|date|after:opening_date',
            'min_education_level' => 'nullable|string',
            'min_division' => 'nullable|string',
            'min_subjects_pass' => 'nullable|integer',
            'min_grade' => 'nullable|string',
            'fee_mode' => 'required|in:FREE,PAID,CONDITIONAL',
            'fee_amount' => 'nullable|numeric|min:0',
            'closed_message' => 'nullable|string',
            'eligibility_message' => 'nullable|string',
            'payment_message' => 'nullable|string',
        ]);
        
        $setting->update(array_merge($validated, [
            'changed_by' => auth()->id(),
            'version' => $setting->version + 1,
        ]));
        
        return redirect()->route('superadmin.config.application-settings.index')
            ->with('success', 'Application settings updated successfully.');
    }

    public function toggleStatus($id)
    {
        $setting = ApplicationSetting::findOrFail($id);
        
        $newStatus = $setting->status === 'OPEN' ? 'CLOSED' : 'OPEN';
        $setting->update([
            'status' => $newStatus,
            'changed_by' => auth()->id(),
        ]);
        
        return redirect()->back()
            ->with('success', "Application status changed to {$newStatus}.");
    }

    public function updateProgramAvailability(Request $request, $id)
    {
        $setting = ApplicationSetting::findOrFail($id);
        
        $request->validate([
            'programs' => 'required|array',
            'programs.*.program_id' => 'required|exists:programs,id',
            'programs.*.is_active' => 'boolean',
            'programs.*.intake_allowed' => 'in:March,September,Both',
            'programs.*.capacity' => 'nullable|integer|min:1',
        ]);
        
        DB::transaction(function () use ($setting, $request) {
            // Delete existing availabilities
            $setting->availablePrograms()->delete();
            
            // Create new ones
            foreach ($request->programs as $programData) {
                ProgramAvailability::create([
                    'application_setting_id' => $setting->id,
                    'program_id' => $programData['program_id'],
                    'is_active' => $programData['is_active'] ?? true,
                    'intake_allowed' => $programData['intake_allowed'] ?? 'Both',
                    'capacity' => $programData['capacity'] ?? null,
                ]);
            }
        });
        
        return redirect()->back()
            ->with('success', 'Program availability updated successfully.');
    }

    private function getAcademicYears()
    {
        $currentYear = date('Y');
        $years = [];
        
        for ($i = -2; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $years[] = "{$year}/" . ($year + 1);
        }
        
        return $years;
    }
}