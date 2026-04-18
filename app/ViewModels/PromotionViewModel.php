<?php
// app/ViewModels/PromotionViewModel.php

namespace App\ViewModels;

use App\Models\Student;
use App\Models\Programme;
use App\Models\AcademicYear;
use App\Services\PromotionService;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionViewModel
{
    protected $promotionService;
    
    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }
    
    public function forSemester($request, Programme $programme, AcademicYear $academicYear)
    {
        $query = Student::with(['user', 'programme'])
            ->where('programme_id', $programme->id)
            ->where('status', 'active');
        
        // Apply filters (server-side)
        if ($request->filled('level')) {
            $query->where('current_level', $request->level);
        }
        
        if ($request->filled('semester')) {
            $query->where('current_semester', $request->semester);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'LIKE', "%{$search}%")
                         ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // Status filter (eligibility)
        $students = $query->paginate(20);
        
        // Calculate eligibility for each student
        $eligibleStudents = [];
        foreach ($students as $student) {
            $eligibility = $this->promotionService->isEligibleForSemesterPromotion($student);
            $eligibleStudents[$student->id] = $eligibility;
        }
        
        // Apply eligibility filter if needed
        if ($request->filled('eligibility_status')) {
            $students->setCollection($students->getCollection()->filter(function($student) use ($eligibleStudents, $request) {
                $isEligible = $eligibleStudents[$student->id]['eligible'] ?? false;
                $status = $request->eligibility_status;
                
                if ($status === 'eligible') return $isEligible;
                if ($status === 'ineligible') return !$isEligible;
                if ($status === 'probation') {
                    $gpa = $eligibleStudents[$student->id]['gpa'] ?? 0;
                    return $gpa >= 1.5 && $gpa < 2.0;
                }
                return true;
            }));
        }
        
        return [
            'students' => $students,
            'eligibleStudents' => $eligibleStudents,
            'programme' => $programme,
            'academicYear' => $academicYear,
            'filters' => $request->only(['level', 'semester', 'eligibility_status', 'search'])
        ];
    }
    
    public function statistics($students, $eligibleStudents)
    {
        return [
            'total' => $students->total(),
            'eligible' => collect($eligibleStudents)->filter(fn($e) => isset($e['eligible']) && $e['eligible'])->count(),
            'ineligible' => collect($eligibleStudents)->filter(fn($e) => !(isset($e['eligible']) && $e['eligible']))->count(),
            'probation' => collect($eligibleStudents)->filter(fn($e) => isset($e['gpa']) && $e['gpa'] < 2.0 && $e['gpa'] >= 1.5)->count()
        ];
    }
}