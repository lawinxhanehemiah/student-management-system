<?php
// app/Services/GPACalculatorService.php



namespace App\Services;

use App\Models\StudentResult;
use App\Models\Student;
use App\Models\AcademicYear;

class GPACalculatorService
{
    /**
     * Calculate GPA for a student
     * Formula: GPA = Σ(Grade Point × Credit Hours) / Σ(Credit Hours)
     */
    public function calculateGPA($studentId, $academicYearId = null, $semester = null)
    {
        $query = StudentResult::with(['module'])
            ->where('student_id', $studentId)
            ->where('workflow_status', 'published')
            ->where('result_status', 'pass');
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        if ($semester) {
            $query->where('semester', $semester);
        }
        
        $results = $query->get();
        
        if ($results->isEmpty()) {
            return [
                'total_points' => 0,
                'total_credits' => 0,
                'gpa' => 0.00,
                'classification' => 'N/A',
                'results_count' => 0
            ];
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($results as $result) {
            $credits = (float) ($result->module->default_credits ?? 3);
            $gradePoint = (float) ($result->grade_point ?? 0);
            
            $totalPoints += $gradePoint * $credits;
            $totalCredits += $credits;
        }
        
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;
        
        $classification = $this->getClassification($gpa);
        
        return [
            'total_points' => round($totalPoints, 2),
            'total_credits' => $totalCredits,
            'gpa' => $gpa,
            'classification' => $classification,
            'results_count' => $results->count()
        ];
    }
    
    /**
     * Calculate Semester GPA
     */
    public function calculateSemesterGPA($studentId, $academicYearId, $semester)
    {
        return $this->calculateGPA($studentId, $academicYearId, $semester);
    }
    
    /**
     * Calculate Cumulative GPA (CGPA) - All semesters
     */
    public function calculateCGPA($studentId)
    {
        return $this->calculateGPA($studentId);
    }
    
    /**
     * Get classification based on GPA
     */
    private function getClassification($gpa)
    {
        if ($gpa >= 4.5) return 'First Class Honours';
        if ($gpa >= 3.5) return 'Second Class Honours (Upper)';
        if ($gpa >= 2.5) return 'Second Class Honours (Lower)';
        if ($gpa >= 2.0) return 'Pass';
        return 'Fail';
    }
    
    /**
     * Get student transcript with all results and GPA
     */
    public function getTranscript($studentId, $academicYearId = null)
    {
        $student = Student::with(['user', 'programme'])->find($studentId);
        
        $query = StudentResult::with(['module', 'academicYear'])
            ->where('student_id', $studentId)
            ->where('workflow_status', 'published')
            ->orderBy('academic_year_id')
            ->orderBy('semester');
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        $results = $query->get();
        
        // Group by academic year and semester
        $grouped = [];
        
        foreach ($results as $result) {
            $yearId = $result->academic_year_id;
            $semester = $result->semester;
            $yearName = $result->academicYear->name ?? 'Unknown';
            
            if (!isset($grouped[$yearId])) {
                $grouped[$yearId] = [
                    'year_id' => $yearId,
                    'year_name' => $yearName,
                    'semester_1' => [],
                    'semester_2' => [],
                    'gpa_semester_1' => 0,
                    'gpa_semester_2' => 0,
                    'year_gpa' => 0
                ];
            }
            
            if ($semester == 1) {
                $grouped[$yearId]['semester_1'][] = $result;
            } else {
                $grouped[$yearId]['semester_2'][] = $result;
            }
        }
        
        // Calculate GPAs for each semester and year
        foreach ($grouped as $yearId => &$yearData) {
            if (!empty($yearData['semester_1'])) {
                $gpa1 = $this->calculateGPAByResults($yearData['semester_1']);
                $yearData['gpa_semester_1'] = $gpa1['gpa'];
                $yearData['semester_1'] = $this->formatResults($yearData['semester_1']);
            }
            
            if (!empty($yearData['semester_2'])) {
                $gpa2 = $this->calculateGPAByResults($yearData['semester_2']);
                $yearData['gpa_semester_2'] = $gpa2['gpa'];
                $yearData['semester_2'] = $this->formatResults($yearData['semester_2']);
            }
            
            // Year GPA = average of both semesters
            $allResults = [];
            if (!empty($yearData['semester_1'])) $allResults = array_merge($allResults, $yearData['semester_1']);
            if (!empty($yearData['semester_2'])) $allResults = array_merge($allResults, $yearData['semester_2']);
            
            if (!empty($allResults)) {
                $yearGPA = $this->calculateGPAByResults($allResults);
                $yearData['year_gpa'] = $yearGPA['gpa'];
            }
        }
        
        // Calculate overall CGPA
        $cgpa = $this->calculateGPAByResults($results);
        
        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? 'N/A',
                'registration_number' => $student->registration_number,
                'nacte_reg_number' => $student->nacte_reg_number,
                'programme' => $student->programme->name ?? 'N/A'
            ],
            'results' => array_values($grouped),
            'cgpa' => $cgpa['gpa'],
            'total_credits' => $cgpa['total_credits'],
            'total_points' => $cgpa['total_points'],
            'classification' => $this->getClassification($cgpa['gpa'])
        ];
    }
    
    /**
     * Calculate GPA from a collection of results
     */
    private function calculateGPAByResults($results)
    {
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($results as $result) {
            $credits = (float) ($result->module->default_credits ?? 3);
            $gradePoint = (float) ($result->grade_point ?? 0);
            
            $totalPoints += $gradePoint * $credits;
            $totalCredits += $credits;
        }
        
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
        
        return [
            'total_points' => round($totalPoints, 2),
            'total_credits' => $totalCredits,
            'gpa' => $gpa
        ];
    }
    
    /**
     * Format results for display
     */
    private function formatResults($results)
    {
        $formatted = [];
        foreach ($results as $result) {
            $formatted[] = [
                'id' => $result->id,
                'module_code' => $result->module->code ?? 'N/A',
                'module_name' => $result->module->name ?? 'N/A',
                'credits' => $result->module->default_credits ?? 3,
                'cw_score' => $result->raw_cw,
                'exam_score' => $result->raw_exam,
                'total_score' => $result->weighted_score,
                'grade' => $result->grade,
                'grade_point' => $result->grade_point,
                'remark' => $result->remark
            ];
        }
        return $formatted;
    }
}