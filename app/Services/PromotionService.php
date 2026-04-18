<?php
// app/Services/PromotionService.php

namespace App\Services;

use App\Models\Student;
use App\Models\PromotionLog;
use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionService
{
    protected $passMark = 2.0; // GPA 2.0 = C pass (NACTEVET standard)
    protected $probationThreshold = 1.5;
    protected $chunkSize = 50;
    
    /**
     * Get current academic year
     */
    protected function getCurrentAcademicYear()
    {
        return AcademicYear::where('is_active', true)->first();
    }
    
    /**
     * Check if student is eligible for semester promotion
     */
    public function isEligibleForSemesterPromotion(Student $student)
    {
        $conditionsMet = [];
        $conditionsFailed = [];
        $eligible = true;
        
        // Condition 1: Student must be ACTIVE
        if ($student->status !== 'active') {
            $conditionsFailed[] = 'Student is not active';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Student is active';
        }
        
        // Condition 2: Must have completed current semester
        $currentSemester = $student->current_semester;
        $semesterCompleted = $currentSemester == 1 
            ? $student->semester_1_completed 
            : $student->semester_2_completed;
            
        if (!$semesterCompleted) {
            $conditionsFailed[] = 'Current semester not completed';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Current semester completed';
        }
        
        // Condition 3: Must have results for all courses
        $hasResults = $this->hasAllCourseResults($student);
        if (!$hasResults) {
            $conditionsFailed[] = 'Missing results for some courses';
            $eligible = false;
        } else {
            $conditionsMet[] = 'All course results available';
        }
        
        // Condition 4: GPA must meet pass mark
        $gpa = $this->calculateSemesterGPA($student);
        if ($gpa < $this->passMark) {
            $conditionsFailed[] = "GPA ({$gpa}) below pass mark ({$this->passMark})";
            $eligible = false;
        } else {
            $conditionsMet[] = "GPA ({$gpa}) meets pass mark";
        }
        
        // Condition 5: Fee clearance
        $feeCleared = $this->checkFeeClearance($student);
        if (!$feeCleared) {
            $conditionsFailed[] = 'Fee balance outstanding';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Fee balance cleared';
        }
        
        return [
            'eligible' => $eligible,
            'conditions' => $conditionsFailed,
            'conditions_met' => $conditionsMet,
            'gpa' => $gpa,
            'fee_cleared' => $feeCleared
        ];
    }
    
    /**
     * Check if student is eligible for level promotion
     */
    public function isEligibleForLevelPromotion(Student $student)
    {
        $conditionsMet = [];
        $conditionsFailed = [];
        $eligible = true;
        
        // Condition 1: Student must be ACTIVE
        if ($student->status !== 'active') {
            $conditionsFailed[] = 'Student is not active';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Student is active';
        }
        
        // Condition 2: Both semesters must be completed
        $bothSemestersCompleted = $student->semester_1_completed && $student->semester_2_completed;
        if (!$bothSemestersCompleted) {
            $conditionsFailed[] = 'Both semesters not completed';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Both semesters completed';
        }
        
        // Condition 3: Must have results for all courses in both semesters
        $hasResults = $this->hasAllCourseResultsForYear($student);
        if (!$hasResults) {
            $conditionsFailed[] = 'Missing results for some courses';
            $eligible = false;
        } else {
            $conditionsMet[] = 'All course results available';
        }
        
        // Condition 4: Cumulative GPA must meet pass mark
        $cgpa = $this->calculateCumulativeGPA($student);
        if ($cgpa < $this->passMark) {
            $conditionsFailed[] = "CGPA ({$cgpa}) below pass mark ({$this->passMark})";
            $eligible = false;
        } else {
            $conditionsMet[] = "CGPA ({$cgpa}) meets pass mark";
        }
        
        // Condition 5: Fee clearance
        $feeCleared = $this->checkFeeClearance($student);
        if (!$feeCleared) {
            $conditionsFailed[] = 'Fee balance outstanding';
            $eligible = false;
        } else {
            $conditionsMet[] = 'Fee balance cleared';
        }
        
        return [
            'eligible' => $eligible,
            'conditions' => $conditionsFailed,
            'conditions_met' => $conditionsMet,
            'cgpa' => $cgpa,
            'fee_cleared' => $feeCleared
        ];
    }
    
    /**
     * Promote student by semester
     */
    public function promoteBySemester(Student $student, $promotedBy = null)
    {
        $eligibility = $this->isEligibleForSemesterPromotion($student);
        
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => 'Student not eligible for promotion',
                'conditions' => $eligibility['conditions']
            ];
        }
        
        try {
            DB::beginTransaction();
            
            $currentSemester = $student->current_semester;
            $fromLevel = $student->current_level;
            $toLevel = $fromLevel;
            $fromSemester = $currentSemester;
            $toSemester = $currentSemester == 1 ? 2 : 1;
            
            // If moving to next level (semester 2 -> semester 1 of next level)
            if ($currentSemester == 2) {
                $toLevel = $fromLevel + 1;
                $toSemester = 1;
            }
            
            // Update student
            $student->current_semester = $toSemester;
            if ($toLevel > $fromLevel) {
                $student->current_level = $toLevel;
            }
            
            // Reset semester completion flags
            $student->semester_1_completed = false;
            $student->semester_2_completed = false;
            
            $student->save();
            
            // Log promotion
            PromotionLog::create([
                'student_id' => $student->id,
                'academic_year_id' => $student->academic_year_id,
                'from_level' => $fromLevel,
                'to_level' => $toLevel,
                'from_semester' => $fromSemester,
                'to_semester' => $toSemester,
                'promotion_type' => 'semester',
                'gpa' => $eligibility['gpa'],
                'fee_cleared' => $eligibility['fee_cleared'],
                'conditions_met' => json_encode($eligibility['conditions_met']),
                'promoted_by' => $promotedBy ?? auth()->id(),
                'promoted_at' => now()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Student promoted from Semester {$fromSemester} to Semester {$toSemester}",
                'from_level' => $fromLevel,
                'to_level' => $toLevel,
                'from_semester' => $fromSemester,
                'to_semester' => $toSemester
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Semester promotion failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to promote student: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Promote student by level
     */
    public function promoteByLevel(Student $student, $promotedBy = null)
    {
        $eligibility = $this->isEligibleForLevelPromotion($student);
        
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => 'Student not eligible for level promotion',
                'conditions' => $eligibility['conditions']
            ];
        }
        
        try {
            DB::beginTransaction();
            
            $fromLevel = $student->current_level;
            $toLevel = $fromLevel + 1;
            
            // Update student
            $student->current_level = $toLevel;
            $student->current_semester = 1;
            $student->semester_1_completed = false;
            $student->semester_2_completed = false;
            $student->cumulative_gpa = $eligibility['cgpa'];
            $student->save();
            
            // Log promotion
            PromotionLog::create([
                'student_id' => $student->id,
                'academic_year_id' => $student->academic_year_id,
                'from_level' => $fromLevel,
                'to_level' => $toLevel,
                'promotion_type' => 'level',
                'gpa' => $eligibility['cgpa'],
                'fee_cleared' => $eligibility['fee_cleared'],
                'conditions_met' => json_encode($eligibility['conditions_met']),
                'promoted_by' => $promotedBy ?? auth()->id(),
                'promoted_at' => now()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => "Student promoted from Year {$fromLevel} to Year {$toLevel}",
                'from_level' => $fromLevel,
                'to_level' => $toLevel
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Level promotion failed', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to promote student: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Bulk promotion
     */
    public function bulkPromotion($students, $promotionType, $promotedBy = null)
    {
        $results = [
            'successful' => [],
            'failed' => [],
            'total' => count($students),
            'eligible' => 0,
            'ineligible' => 0
        ];
        
        foreach ($students as $student) {
            if ($promotionType == 'semester') {
                $result = $this->promoteBySemester($student, $promotedBy);
            } else {
                $result = $this->promoteByLevel($student, $promotedBy);
            }
            
            if ($result['success']) {
                $results['successful'][] = [
                    'student_id' => $student->id,
                    'name' => $student->user->first_name . ' ' . $student->user->last_name,
                    'reg_no' => $student->registration_number,
                    'message' => $result['message']
                ];
                $results['eligible']++;
            } else {
                $results['failed'][] = [
                    'student_id' => $student->id,
                    'name' => $student->user->first_name . ' ' . $student->user->last_name,
                    'reg_no' => $student->registration_number,
                    'message' => $result['message'],
                    'conditions' => $result['conditions'] ?? []
                ];
                $results['ineligible']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Helper: Check if student has all course results for current semester
     */
    private function hasAllCourseResults(Student $student)
    {
        $registrations = CourseRegistration::where('student_id', $student->id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('semester', $student->current_semester)
            ->with('results')
            ->get();
        
        foreach ($registrations as $reg) {
            if (!$reg->results || $reg->results->grade_point === null) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Helper: Check if student has all course results for the whole year
     */
    private function hasAllCourseResultsForYear(Student $student)
    {
        $registrations = CourseRegistration::where('student_id', $student->id)
            ->where('academic_year_id', $student->academic_year_id)
            ->with('results')
            ->get();
        
        foreach ($registrations as $reg) {
            if (!$reg->results || $reg->results->grade_point === null) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Helper: Calculate semester GPA
     */
    private function calculateSemesterGPA(Student $student)
    {
        $registrations = CourseRegistration::where('student_id', $student->id)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('semester', $student->current_semester)
            ->with('results', 'course')
            ->get();
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($registrations as $reg) {
            if ($reg->results && $reg->results->grade_point !== null) {
                $creditHours = $reg->course->credit_hours ?? 3;
                $totalPoints += $reg->results->grade_point * $creditHours;
                $totalCredits += $creditHours;
            }
        }
        
        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
    }
    
    /**
     * Helper: Calculate cumulative GPA (ALL YEARS)
     */
    private function calculateCumulativeGPA(Student $student)
    {
        $registrations = CourseRegistration::where('student_id', $student->id)
            ->with('results', 'course', 'academicYear')
            ->get();
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($registrations as $reg) {
            if ($reg->results && $reg->results->grade_point !== null) {
                $creditHours = $reg->course->credit_hours ?? 3;
                $totalPoints += $reg->results->grade_point * $creditHours;
                $totalCredits += $creditHours;
            }
        }
        
        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
    }
    
    /**
     * Helper: Check fee clearance
     */
    private function checkFeeClearance(Student $student)
    {
        $currentYear = $this->getCurrentAcademicYear();
        
        if (!$currentYear) {
            return false;
        }
        
        $totalBalance = Invoice::where('student_id', $student->id)
            ->where('academic_year_id', $currentYear->id)
            ->sum('balance');
        
        return $totalBalance <= 0;
    }
}