<?php
// app/Http/Controllers/Hod/PromotionController.php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromotionRequest;
use App\Models\Department;
use App\Models\Programme;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\PromotionLog;
use App\Services\PromotionService;
use App\Services\PromotionDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    protected $promotionService;
    protected $dataService;
    
    public function __construct(PromotionService $promotionService, PromotionDataService $dataService)
    {
        $this->promotionService = $promotionService;
        $this->dataService = $dataService;
    }
    
    /**
     * Semester promotion page
     */
    public function semesterPromotion(Request $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        $query = Student::with(['user', 'programme', 'invoices'])
            ->where('programme_id', $programme->id)
            ->where('status', 'active');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Get students with eligibility data
        $students = $this->dataService->getStudentsWithEligibility($query, 'semester');
        
        // Apply status filter after eligibility calculation
        if ($request->filled('eligibility_status')) {
            $students->setCollection($students->getCollection()->filter(function($student) use ($request) {
                return $this->filterByStatus($student, $request->eligibility_status);
            }));
        }
        
        $stats = $this->dataService->getStatistics($students);
        $filterOptions = $this->dataService->getFilterOptions();
        
        return view('hod.promotion.semester', [
            'students' => $students,
            'eligibleStudents' => $students->mapWithKeys(function($student) {
                return [$student->id => $student->eligibility_data];
            })->toArray(),
            'stats' => $stats,
            'programme' => $programme,
            'academicYear' => $academicYear,
            'filters' => $request->only(['level', 'semester', 'eligibility_status', 'search']),
            'levels' => $filterOptions['levels'],
            'semesters' => $filterOptions['semesters'],
            'statuses' => $filterOptions['statuses']
        ]);
    }
    
    /**
     * Year promotion page
     */
    public function yearPromotion(Request $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        $query = Student::with(['user', 'programme', 'invoices'])
            ->where('programme_id', $programme->id)
            ->where('status', 'active');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Get students with eligibility data for level promotion
        $students = $this->dataService->getStudentsWithEligibility($query, 'level');
        
        // Apply status filter after eligibility calculation
        if ($request->filled('eligibility_status')) {
            $students->setCollection($students->getCollection()->filter(function($student) use ($request) {
                return $this->filterByStatus($student, $request->eligibility_status);
            }));
        }
        
        $stats = $this->dataService->getStatistics($students);
        $filterOptions = $this->dataService->getFilterOptions();
        
        return view('hod.promotion.year', [
            'students' => $students,
            'eligibleStudents' => $students->mapWithKeys(function($student) {
                return [$student->id => $student->eligibility_data];
            })->toArray(),
            'stats' => $stats,
            'programme' => $programme,
            'academicYear' => $academicYear,
            'filters' => $request->only(['level', 'semester', 'eligibility_status', 'search']),
            'levels' => $filterOptions['levels'],
            'semesters' => $filterOptions['semesters'],
            'statuses' => $filterOptions['statuses']
        ]);
    }
    
    /**
     * Process semester promotion
     */
    public function processSemesterPromotion(PromotionRequest $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No programme found'], 422);
            }
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $students = Student::whereIn('id', $request->student_ids)
            ->where('programme_id', $programme->id)
            ->get();
        
        $results = $this->promotionService->bulkPromotion($students, 'semester');
        
        if ($request->ajax()) {
            return response()->json($results);
        }
        
        return redirect()->route('hod.promotion.results')
            ->with('results', $results)
            ->with('type', 'semester');
    }
    
    /**
     * Process year promotion
     */
    public function processYearPromotion(PromotionRequest $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No programme found'], 422);
            }
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $students = Student::whereIn('id', $request->student_ids)
            ->where('programme_id', $programme->id)
            ->get();
        
        $results = $this->promotionService->bulkPromotion($students, 'level');
        
        if ($request->ajax()) {
            return response()->json($results);
        }
        
        return redirect()->route('hod.promotion.results')
            ->with('results', $results)
            ->with('type', 'level');
    }
    
    /**
     * Bulk promotion page
     */
    public function bulkPromotion(Request $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $students = Student::with(['user', 'programme'])
            ->where('programme_id', $programme->id)
            ->where('status', 'active')
            ->get();
        
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        return view('hod.promotion.bulk', compact('students', 'academicYear', 'programme'));
    }
    
    /**
     * Process bulk promotion
     */
    public function processBulkPromotion(Request $request)
    {
        $request->validate([
            'promotion_type' => 'required|in:semester,level',
            'student_ids' => 'required_if:select_all,false|array',
            'select_all' => 'sometimes|boolean'
        ]);
        
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return response()->json(['success' => false, 'message' => 'No programme found'], 422);
        }
        
        $query = Student::where('programme_id', $programme->id)
            ->where('status', 'active');
        
        if ($request->select_all) {
            $students = $query->get();
        } else {
            $students = $query->whereIn('id', $request->student_ids)->get();
        }
        
        $results = $this->promotionService->bulkPromotion($students, $request->promotion_type);
        
        if ($request->ajax()) {
            return response()->json($results);
        }
        
        return redirect()->route('hod.promotion.results')
            ->with('results', $results)
            ->with('type', $request->promotion_type);
    }
    
    /**
     * Promotion history page
     */
    public function promotionHistory(Request $request)
    {
        $department = $this->getDepartment();
        $programme = $this->getProgrammeByDepartment($department);
        
        if (!$programme) {
            return redirect()->back()->with('error', 'No programme found for your department');
        }
        
        $history = PromotionLog::with(['student.user', 'promotedBy'])
            ->whereHas('student', function($q) use ($programme) {
                $q->where('programme_id', $programme->id);
            })
            ->orderBy('promoted_at', 'desc')
            ->paginate(20);
        
        return view('hod.promotion.history', compact('history', 'programme'));
    }
    
    /**
     * Promotion results page
     */
    public function promotionResults(Request $request)
    {
        $type = $request->get('type', 'semester');
        $successful = session('results.successful', []);
        $failed = session('results.failed', []);
        $eligible = session('results.eligible', 0);
        $ineligible = session('results.ineligible', 0);
        $total = session('results.total', 0);
        
        return view('hod.promotion.results', compact(
            'type', 'successful', 'failed', 'eligible', 'ineligible', 'total'
        ));
    }
    
    /**
     * Apply filters to query
     */
    protected function applyFilters($query, Request $request)
    {
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
    }
    
    /**
     * Filter by eligibility status
     */
    protected function filterByStatus($student, $status)
    {
        $eligibility = $student->eligibility_data;
        $isEligible = $eligibility['eligible'] ?? false;
        $gpa = $eligibility['gpa'] ?? 0;
        
        switch ($status) {
            case 'eligible':
                return $isEligible;
            case 'ineligible':
                return !$isEligible;
            case 'probation':
                return !$isEligible && $gpa >= 1.5 && $gpa < 2.0;
            default:
                return true;
        }
    }
    
    /**
     * Get department
     */
    protected function getDepartment()
    {
        return Department::find(auth()->user()->department_id);
    }
    
    /**
     * Get programme by department
     */
    protected function getProgrammeByDepartment($department)
    {
        if (!$department) return null;
        
        $programmeCode = match($department->code) {
            'PST' => 'PST',
            'CMT' => 'CMT',
            'BA' => 'BAT',
            'SW' => 'SWT',
            'CD' => 'CDT',
            default => null
        };
        
        return Programme::where('code', $programmeCode)->first();
    }
}