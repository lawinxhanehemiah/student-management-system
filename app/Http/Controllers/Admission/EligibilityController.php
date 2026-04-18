<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use App\Models\EligibilityRule;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EligibilityController extends Controller
{
    /**
     * Display eligibility rules management page
     */
    public function rules()
    {
        $rules = DB::table('eligibility_rules as er')
            ->select('er.*', 'p.name as programme_name', 'p.code as programme_code')
            ->leftJoin('programmes as p', 'er.programme_id', '=', 'p.id')
            ->orderBy('p.name')
            ->paginate(10);
        
        $programmes = DB::table('programmes')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();
        
        $categories = $this->getCategories();
        $entryLevels = $this->getEntryLevels();
        $divisions = $this->getDivisions();
        $operators = $this->getOperators();
        
        return view('admission.eligibility.rules', compact(
            'rules', 
            'programmes', 
            'categories', 
            'entryLevels', 
            'divisions', 
            'operators'
        ));
    }
    
    /**
     * Store eligibility rule
     */
    public function storeRule(Request $request)
    {
        $validator = $this->validateRule($request);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }
        
        try {
            DB::beginTransaction();
            
            // Check if rule already exists for this programme
            $existingRule = DB::table('eligibility_rules')
                ->where('programme_id', $request->programme_id)
                ->first();
            
            if ($existingRule) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'An eligibility rule already exists for this programme. Please edit the existing rule instead.')
                    ->withInput();
            }
            
            $ruleData = $this->prepareRuleData($request);
            $ruleData['created_by'] = auth()->id();
            $ruleData['created_at'] = now();
            
            DB::table('eligibility_rules')->insert($ruleData);
            
            DB::commit();
            
            Log::info('Eligibility rule added', [
                'programme_id' => $request->programme_id,
                'created_by' => auth()->id()
            ]);
            
            return redirect()->route('admission.eligibility.rules')
                ->with('success', 'Eligibility rule added successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add eligibility rule: ' . $e->getMessage(), [
                'programme_id' => $request->programme_id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to add rule: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Update eligibility rule
     */
    public function updateRule(Request $request, $id)
    {
        $validator = $this->validateRule($request, false);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $rule = DB::table('eligibility_rules')->where('id', $id)->first();
            
            if (!$rule) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Eligibility rule not found'
                ], 404);
            }
            
            $ruleData = $this->prepareRuleData($request);
            $ruleData['updated_at'] = now();
            
            DB::table('eligibility_rules')
                ->where('id', $id)
                ->update($ruleData);
            
            DB::commit();
            
            Log::info('Eligibility rule updated', [
                'rule_id' => $id,
                'programme_id' => $rule->programme_id,
                'updated_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Rule updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update rule: ' . $e->getMessage(), [
                'rule_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update rule: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete eligibility rule
     */
    public function deleteRule($id)
    {
        try {
            DB::beginTransaction();
            
            $rule = DB::table('eligibility_rules')->where('id', $id)->first();
            
            if (!$rule) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Eligibility rule not found'
                ], 404);
            }
            
            // Check if rule is being used by any applications
            $usageCount = DB::table('applications')
                ->where('selected_program_id', $rule->programme_id)
                ->whereIn('status', ['submitted', 'pending', 'approved'])
                ->count();
            
            if ($usageCount > 0) {
                // Soft delete or just deactivate instead of hard delete
                DB::table('eligibility_rules')
                    ->where('id', $id)
                    ->update([
                        'is_active' => 0,
                        'updated_at' => now()
                    ]);
                
                $message = "Rule deactivated because it's being used by {$usageCount} application(s). You can reactivate it later.";
                
                Log::info('Eligibility rule deactivated (soft delete)', [
                    'rule_id' => $id,
                    'programme_id' => $rule->programme_id,
                    'usage_count' => $usageCount,
                    'deleted_by' => auth()->id()
                ]);
            } else {
                // Hard delete if not in use
                DB::table('eligibility_rules')->where('id', $id)->delete();
                $message = 'Rule deleted successfully';
                
                Log::info('Eligibility rule deleted', [
                    'rule_id' => $id,
                    'programme_id' => $rule->programme_id,
                    'deleted_by' => auth()->id()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true, 
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete rule: ' . $e->getMessage(), [
                'rule_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete rule: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get single eligibility rule (for editing)
     */
    public function getRule($id)
    {
        try {
            $rule = DB::table('eligibility_rules')
                ->where('id', $id)
                ->first();
            
            if (!$rule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule not found'
                ], 404);
            }
            
            // Decode JSON fields
            $rule->core_subjects = json_decode($rule->core_subjects, true) ?? [];
            $rule->alternative_subjects = json_decode($rule->alternative_subjects, true) ?? [];
            
            return response()->json([
                'success' => true,
                'data' => $rule
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rule'
            ], 500);
        }
    }
    
    /**
     * Toggle rule status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        try {
            $rule = DB::table('eligibility_rules')->where('id', $id)->first();
            
            if (!$rule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rule not found'
                ], 404);
            }
            
            $newStatus = $rule->is_active ? 0 : 1;
            
            DB::table('eligibility_rules')
                ->where('id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);
            
            Log::info('Eligibility rule status toggled', [
                'rule_id' => $id,
                'new_status' => $newStatus,
                'updated_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => $newStatus ? 'Rule activated' : 'Rule deactivated'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to toggle rule status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rule status'
            ], 500);
        }
    }
    
    /**
     * Bulk delete rules
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rule_ids' => 'required|array',
            'rule_ids.*' => 'exists:eligibility_rules,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid rule IDs provided',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $deletedCount = 0;
            $deactivatedCount = 0;
            
            foreach ($request->rule_ids as $ruleId) {
                $rule = DB::table('eligibility_rules')->where('id', $ruleId)->first();
                
                if (!$rule) continue;
                
                $usageCount = DB::table('applications')
                    ->where('selected_program_id', $rule->programme_id)
                    ->whereIn('status', ['submitted', 'pending', 'approved'])
                    ->count();
                
                if ($usageCount > 0) {
                    DB::table('eligibility_rules')
                        ->where('id', $ruleId)
                        ->update(['is_active' => 0]);
                    $deactivatedCount++;
                } else {
                    DB::table('eligibility_rules')->where('id', $ruleId)->delete();
                    $deletedCount++;
                }
            }
            
            DB::commit();
            
            $message = "{$deletedCount} rule(s) deleted, {$deactivatedCount} rule(s) deactivated (in use)";
            
            Log::info('Bulk eligibility rule operation', [
                'deleted' => $deletedCount,
                'deactivated' => $deactivatedCount,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount,
                'deactivated' => $deactivatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed'
            ], 500);
        }
    }
    
    /**
     * Validate rule data
     */
    private function validateRule(Request $request, $isCreate = true)
    {
        $rules = [
            'category' => ['nullable', 'string', Rule::in($this->getCategories())],
            'min_csee_points' => 'nullable|integer|min:7|max:36',
            'points_operator' => ['nullable', 'string', Rule::in($this->getOperators())],
            'min_csee_division' => ['nullable', 'string', Rule::in($this->getDivisions())],
            'core_subjects' => 'nullable|array',
            'core_subjects.*' => 'string|max:100',
            'alternative_subjects' => 'nullable|array',
            'alternative_subjects.*' => 'string|max:100',
            'min_alternative_count' => 'nullable|integer|min:0|max:10',
            'min_subject_grade' => 'nullable|string|max:2',
            'min_acsee_principal_passes' => 'nullable|integer|min:0|max:3',
            'entry_level' => ['nullable', 'string', Rule::in($this->getEntryLevels())],
            'is_active' => 'nullable|boolean',
        ];
        
        if ($isCreate) {
            $rules['programme_id'] = 'required|exists:programmes,id';
        }
        
        return Validator::make($request->all(), $rules);
    }
    
    /**
     * Prepare rule data for database
     */
    private function prepareRuleData(Request $request)
    {
        return [
            'programme_id' => $request->programme_id,
            'category' => $request->category ?? 'general',
            'min_csee_points' => $request->min_csee_points,
            'points_operator' => $request->points_operator ?? 'lte',
            'min_csee_division' => $request->min_csee_division,
            'core_subjects' => json_encode($request->core_subjects ?? []),
            'alternative_subjects' => json_encode($request->alternative_subjects ?? []),
            'min_alternative_count' => $request->min_alternative_count ?? 1,
            'min_subject_grade' => $request->min_subject_grade ?? 'D',
            'min_acsee_principal_passes' => $request->min_acsee_principal_passes ?? 0,
            'entry_level' => $request->entry_level ?? 'CSEE',
            'is_active' => $request->is_active ?? 1,
            'updated_at' => now(),
        ];
    }
    
    /**
     * Get available categories
     */
    private function getCategories()
    {
        return ['health', 'non_health', 'general'];
    }
    
    /**
     * Get available entry levels
     */
    private function getEntryLevels()
    {
        return ['CSEE', 'ACSEE', 'Diploma', 'Degree'];
    }
    
    /**
     * Get available divisions
     */
    private function getDivisions()
    {
        return ['I', 'II', 'III', 'IV'];
    }
    
    /**
     * Get available operators
     */
    private function getOperators()
    {
        return ['lte', 'gte'];
    }
    
    /**
     * Clone a rule for a different programme
     */
    public function cloneRule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_rule_id' => 'required|exists:eligibility_rules,id',
            'target_programme_id' => 'required|exists:programmes,id|different:source_programme_id',
            'source_programme_id' => 'required|exists:programmes,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Check if target programme already has a rule
            $existingRule = DB::table('eligibility_rules')
                ->where('programme_id', $request->target_programme_id)
                ->first();
            
            if ($existingRule) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Target programme already has an eligibility rule'
                ], 422);
            }
            
            // Get source rule
            $sourceRule = DB::table('eligibility_rules')
                ->where('id', $request->source_rule_id)
                ->first();
            
            if (!$sourceRule) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Source rule not found'
                ], 404);
            }
            
            // Create new rule
            $newRuleId = DB::table('eligibility_rules')->insertGetId([
                'programme_id' => $request->target_programme_id,
                'category' => $sourceRule->category,
                'min_csee_points' => $sourceRule->min_csee_points,
                'points_operator' => $sourceRule->points_operator,
                'min_csee_division' => $sourceRule->min_csee_division,
                'core_subjects' => $sourceRule->core_subjects,
                'alternative_subjects' => $sourceRule->alternative_subjects,
                'min_alternative_count' => $sourceRule->min_alternative_count,
                'min_subject_grade' => $sourceRule->min_subject_grade,
                'min_acsee_principal_passes' => $sourceRule->min_acsee_principal_passes,
                'entry_level' => $sourceRule->entry_level,
                'is_active' => 1,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Eligibility rule cloned', [
                'source_rule_id' => $request->source_rule_id,
                'source_programme' => $request->source_programme_id,
                'target_programme' => $request->target_programme_id,
                'new_rule_id' => $newRuleId,
                'created_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Rule cloned successfully',
                'rule_id' => $newRuleId
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to clone rule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clone rule: ' . $e->getMessage()
            ], 500);
        }
    }
}