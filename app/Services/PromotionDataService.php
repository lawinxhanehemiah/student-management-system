<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Programme;
use App\Models\AcademicYear;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionDataService
{
    protected $promotionService;
    
    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }
    
    /**
     * Get students with eligibility data
     */
    public function getStudentsWithEligibility($query, $promotionType = 'semester')
    {
        $students = $query->paginate(20);
        
        foreach ($students as $student) {
            if ($promotionType === 'semester') {
                $eligibility = $this->promotionService->isEligibleForSemesterPromotion($student);
                $student->eligibility_data = $eligibility;
            } else {
                $eligibility = $this->promotionService->isEligibleForLevelPromotion($student);
                $student->eligibility_data = $eligibility;
            }
        }
        
        return $students;
    }
    
    /**
     * Get statistics from students collection
     */
    public function getStatistics($students)
    {
        $eligible = 0;
        $ineligible = 0;
        $probation = 0;
        
        foreach ($students as $student) {
            $eligibility = $student->eligibility_data ?? [];
            $isEligible = $eligibility['eligible'] ?? false;
            $gpa = $eligibility['gpa'] ?? $eligibility['cgpa'] ?? 0;
            
            if ($isEligible) {
                $eligible++;
            } else {
                $ineligible++;
            }
            
            if ($gpa >= 1.5 && $gpa < 2.0) {
                $probation++;
            }
        }
        
        $total = $students->total();
        
        return [
            'total' => $total,
            'eligible' => $eligible,
            'ineligible' => $ineligible,
            'probation' => $probation,
            'success_rate' => $total > 0 ? round(($eligible / $total) * 100, 1) : 0
        ];
    }
    
    /**
     * Get filter options for dropdowns
     */
    public function getFilterOptions()
    {
        return [
            'levels' => [1, 2, 3, 4],
            'semesters' => [1, 2],
            'statuses' => [
                'eligible' => 'Eligible',
                'ineligible' => 'Ineligible',
                'probation' => 'Probation'
            ]
        ];
    }
}