<?php
// app/Services/StudentResultService.php

namespace App\Services;

use App\Models\StudentResult;
use App\Models\Module;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\ResultUploadBatch;
use App\Models\GradingSystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentResultsImport;

class StudentResultService
{
    public function __construct()
    {
        // No GradingEngine dependency
    }
    
    /**
     * Get results with filters based on user role
     */
    public function getResults(array $filters, $user)
    {
        $query = StudentResult::with([
            'student.user',
            'student.programme',
            'module.department',
            'academicYear'
        ]);
        
        // Role-based filtering
        if ($user->hasRole('tutor')) {
            $moduleIds = Module::whereHas('courseAllocations', function($q) use ($user, $filters) {
                $q->where('user_id', $user->id);
                if (!empty($filters['academic_year_id'])) {
                    $q->where('academic_year_id', $filters['academic_year_id']);
                }
            })->pluck('id');
            $query->whereIn('module_id', $moduleIds);
        }
        elseif ($user->hasRole('hod')) {
            $query->whereHas('module', function($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }
        
        // Apply filters
        if (!empty($filters['module_id'])) {
            $query->where('module_id', $filters['module_id']);
        }
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }
        if (!empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }
        if (!empty($filters['workflow_status'])) {
            $query->where('workflow_status', $filters['workflow_status']);
        }
        
        $perPage = $filters['per_page'] ?? 50;
        
        return $query->orderBy('id', 'desc')->paginate($perPage);
    }
    
    /**
     * Get single student result
     */
    public function getResult($id)
    {
        return StudentResult::with([
            'student.user',
            'student.programme',
            'module.department',
            'academicYear'
        ])->find($id);
    }
    
    /**
     * Get the correct program category based on NTA level
     * For NTA Level 4 and 5: always use 'all'
     * For NTA Level 6: use student's program category (health/non_health)
     */
    private function getProgramCategoryByNtaLevel($ntaLevel, $student)
    {
        // NTA Level 4 and 5 use 'all' category only
        if ($ntaLevel <= 5) {
            return 'all';
        }
        
        // NTA Level 6 uses specific category
        if ($ntaLevel >= 6 && $student && $student->programme) {
            $category = $student->programme->program_category ?? 'all';
            if (in_array($category, ['health', 'non_health'])) {
                return $category;
            }
        }
        
        return 'all';
    }
    
    /**
     * Create or update single student result
     * FORMULA: Total = CW + Exam (Simple addition, NOT weighted average)
     */
    public function storeOrUpdateResult(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $module = Module::findOrFail($data['module_id']);
            $student = Student::findOrFail($data['student_id']);
            $academicYear = AcademicYear::findOrFail($data['academic_year_id']);
            
            // Check if academic year is locked
            if ($academicYear->is_locked && ($data['workflow_status'] ?? 'draft') === 'approved') {
                return [
                    'success' => false,
                    'message' => 'Cannot approve results for locked academic year'
                ];
            }
            
            // Get NTA level
            $ntaLevel = $module->nta_level;
            
            // Get program category based on NTA level
            $programCategory = $this->getProgramCategoryByNtaLevel($ntaLevel, $student);
            
            // Get raw scores
            $rawCW = (float) ($data['raw_cw'] ?? 0);
            $rawExam = (float) ($data['raw_exam'] ?? 0);
            
            // SIMPLE ADDITION - kama kwenye picha yako!
            // Total = CW + Exam (NOT weighted average)
            $totalScore = round($rawCW + $rawExam, 2);
            
            // Get grade from grading system - WITH nta_level
            $grading = GradingSystem::where('academic_year_id', $academicYear->id)
                ->where('program_category', $programCategory)
                ->where('nta_level', $ntaLevel)
                ->where('is_active', true)
                ->where('min_score', '<=', $totalScore)
                ->where('max_score', '>=', $totalScore)
                ->first();
            
            // If not found and programCategory is not 'all', try 'all' category
            if (!$grading && $programCategory !== 'all') {
                $grading = GradingSystem::where('academic_year_id', $academicYear->id)
                    ->where('program_category', 'all')
                    ->where('nta_level', $ntaLevel)
                    ->where('is_active', true)
                    ->where('min_score', '<=', $totalScore)
                    ->where('max_score', '>=', $totalScore)
                    ->first();
            }
            
            // If still not found, try without nta_level (fallback for older data)
            if (!$grading) {
                $grading = GradingSystem::where('academic_year_id', $academicYear->id)
                    ->where('program_category', $programCategory)
                    ->where('is_active', true)
                    ->where('min_score', '<=', $totalScore)
                    ->where('max_score', '>=', $totalScore)
                    ->first();
            }
            
            // Final fallback
            if (!$grading) {
                $grading = GradingSystem::where('academic_year_id', $academicYear->id)
                    ->where('program_category', 'all')
                    ->where('is_active', true)
                    ->where('min_score', '<=', $totalScore)
                    ->where('max_score', '>=', $totalScore)
                    ->first();
            }
            
            // Get pass mark
            $passMark = $this->getPassMark($module, $student);
            $resultStatus = $totalScore >= $passMark ? 'pass' : 'fail';
            
            // Get remark
            $remark = $this->getRemarkFromScore($totalScore);
            
            // Get weights for display (not used in calculation anymore)
            $cwWeight = (float) ($module->cw_weight ?? 50);
            $examWeight = (float) ($module->exam_weight ?? 50);
            
            // Prepare result data
            $resultData = [
                'raw_cw' => $data['raw_cw'] ?? null,
                'raw_exam' => $data['raw_exam'] ?? null,
                'weighted_score' => $totalScore,  // Now stores total (CW+Exam)
                'grade' => $grading ? $grading->grade : 'F',
                'grade_point' => $grading ? (float) $grading->grade_point : 0.00,
                'remark' => $remark,
                'result_status' => $resultStatus,
                'workflow_status' => $data['workflow_status'] ?? 'draft',
                'attempt_type' => $data['attempt_type'] ?? 'normal',
                'attempt_no' => $data['attempt_no'] ?? 1,
                'calculation_snapshot' => json_encode([
                    'formula' => 'CW + Exam = Total',
                    'cw_score' => $rawCW,
                    'exam_score' => $rawExam,
                    'total_score' => $totalScore,
                    'pass_mark' => $passMark,
                    'program_category' => $programCategory,
                    'nta_level' => $ntaLevel,
                    'grading_system_id' => $grading ? $grading->id : null,
                    'calculated_at' => now()->toDateTimeString()
                ])
            ];
            
            // Create or update
            $result = StudentResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'module_id' => $module->id,
                    'academic_year_id' => $academicYear->id,
                    'semester' => $data['semester'],
                    'attempt_type' => $data['attempt_type'] ?? 'normal',
                    'attempt_no' => $data['attempt_no'] ?? 1,
                ],
                $resultData
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Result saved successfully',
                'data' => $result->load(['student.user', 'module'])
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing result: ' . $e->getMessage(), $data);
            
            return [
                'success' => false,
                'message' => 'Failed to save result: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get pass mark based on NTA level and programme category
     */
    private function getPassMark($module, $student)
    {
        $ntaLevel = $module->nta_level;
        $programCategory = 'non_health';
        
        if ($student && $student->programme) {
            $programCategory = $student->programme->program_category ?? 'non_health';
        }
        
        // NTA Level 6 Non-Health = 45% (special case)
        if ($ntaLevel == 6 && $programCategory == 'non_health') {
            return 45.00;
        }
        
        // Use module pass_mark if set
        if ($module->pass_mark) {
            return (float) $module->pass_mark;
        }
        
        // Default 50%
        return 50.00;
    }
    
    /**
     * Get remark based on score
     */
    private function getRemarkFromScore($score)
    {
        if ($score >= 80) return 'Outstanding';
        if ($score >= 75) return 'Excellent';
        if ($score >= 70) return 'Very Good';
        if ($score >= 65) return 'Good';
        if ($score >= 60) return 'Credit';
        if ($score >= 50) return 'Pass';
        if ($score >= 45) return 'Marginal Pass';
        if ($score >= 40) return 'Supplementary Required';
        return 'Fail - Retake Required';
    }
    
    /**
     * Bulk upload results from Excel/CSV
     */
    public function bulkUploadResults($file, int $userId, int $academicYearId, int $semester, ?int $moduleId = null): array
    {
        DB::beginTransaction();
        
        try {
            $academicYear = AcademicYear::findOrFail($academicYearId);
            
            if ($academicYear->is_locked) {
                return [
                    'success' => false,
                    'message' => 'Cannot upload results for locked academic year'
                ];
            }
            
            $batch = ResultUploadBatch::create([
                'user_id' => $userId,
                'academic_year_id' => $academicYearId,
                'semester' => $semester,
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => 0,
                'success_rows' => 0,
                'failed_rows' => 0,
                'status' => 'processing'
            ]);
            
            $import = new StudentResultsImport($academicYearId, $semester, $batch->id, $moduleId);
            Excel::import($import, $file);
            
            $batch->update([
                'total_rows' => $import->getTotalRows(),
                'success_rows' => $import->getSuccessRows(),
                'failed_rows' => $import->getFailedRows(),
                'status' => 'completed'
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Upload completed: {$import->getSuccessRows()} successful, {$import->getFailedRows()} failed",
                'data' => [
                    'batch_id' => $batch->id,
                    'total_rows' => $import->getTotalRows(),
                    'success_rows' => $import->getSuccessRows(),
                    'failed_rows' => $import->getFailedRows(),
                    'errors' => $import->getErrors()
                ]
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk upload error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Bulk upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update result workflow status
     */
    public function updateWorkflowStatus(int $resultId, string $status, ?int $userId = null): array
    {
        $result = StudentResult::find($resultId);
        
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }
        
        $validStatuses = ['draft', 'approved', 'published'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        if (in_array($status, ['approved', 'published'])) {
            $academicYear = AcademicYear::find($result->academic_year_id);
            if ($academicYear && $academicYear->is_locked) {
                return ['success' => false, 'message' => "Cannot {$status} results for locked academic year"];
            }
        }
        
        $updateData = ['workflow_status' => $status];
        
        if ($status === 'approved') {
            $updateData['approved_by'] = $userId ?? auth()->id();
            $updateData['approved_at'] = now();
        }
        
        if ($status === 'published') {
            $updateData['published_by'] = $userId ?? auth()->id();
            $updateData['published_at'] = now();
        }
        
        $result->update($updateData);
        
        return ['success' => true, 'message' => "Result {$status} successfully", 'data' => $result];
    }
    
    /**
     * Bulk approve results by batch
     */
    public function bulkApproveResults(int $batchId, int $userId): array
    {
        $batch = ResultUploadBatch::find($batchId);
        
        if (!$batch) {
            return ['success' => false, 'message' => 'Batch not found'];
        }
        
        $academicYear = AcademicYear::find($batch->academic_year_id);
        if ($academicYear && $academicYear->is_locked) {
            return ['success' => false, 'message' => 'Cannot approve results for locked academic year'];
        }
        
        DB::beginTransaction();
        
        try {
            StudentResult::where('batch_id', $batchId)->update([
                'workflow_status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now()
            ]);
            
            $batch->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now()
            ]);
            
            DB::commit();
            
            return ['success' => true, 'message' => 'All results in batch approved successfully'];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Failed to approve batch: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete result
     */
    public function deleteResult(int $resultId, bool $force = false): array
    {
        $result = StudentResult::find($resultId);
        
        if (!$result) {
            return ['success' => false, 'message' => 'Result not found'];
        }
        
        if ($result->workflow_status === 'published') {
            return ['success' => false, 'message' => 'Cannot delete published results'];
        }
        
        DB::beginTransaction();
        
        try {
            if ($force) {
                $result->forceDelete();
            } else {
                $result->delete();
            }
            
            DB::commit();
            
            return ['success' => true, 'message' => 'Result deleted successfully'];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Failed to delete result: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get student transcript
     */
    public function getStudentTranscript(int $studentId, ?int $academicYearId = null): array
    {
        $query = StudentResult::with(['module', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('workflow_status', 'published');
            
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        $results = $query->orderBy('academic_year_id')
            ->orderBy('semester')
            ->get();
        
        return [
            'results' => $results,
            'gpa' => $this->calculateGPA($results)
        ];
    }
    
    /**
     * Calculate GPA from results
     * Formula: GPA = Σ(Grade Point × Credits) / Σ(Credits)
     */
    private function calculateGPA($results): array
    {
        if ($results->isEmpty()) {
            return [
                'total_credits' => 0,
                'total_points' => 0,
                'gpa' => 0,
                'classification' => 'N/A'
            ];
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($results as $result) {
            if ($result->result_status === 'pass' && $result->grade_point > 0) {
                $credits = $result->module->default_credits ?? 3;
                $totalPoints += $result->grade_point * $credits;
                $totalCredits += $credits;
            }
        }
        
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
        
        $classification = match(true) {
            $gpa >= 4.5 => 'First Class Honours',
            $gpa >= 3.5 => 'Second Class Honours (Upper)',
            $gpa >= 2.5 => 'Second Class Honours (Lower)',
            $gpa >= 2.0 => 'Pass',
            default => 'Fail'
        };
        
        return [
            'total_credits' => $totalCredits,
            'total_points' => round($totalPoints, 2),
            'gpa' => $gpa,
            'classification' => $classification
        ];
    }
}