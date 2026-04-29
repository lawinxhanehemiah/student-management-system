<?php
// app/Http/Controllers/SuperAdmin/ResultController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\StudentResultService;
use App\Models\AcademicYear;
use App\Models\Module;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Programme;
use App\Models\GradingSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    protected $resultService;
    
    public function __construct(StudentResultService $resultService)
    {
        $this->resultService = $resultService;
    }
    
    /**
     * Display main results page with filters
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $allModules = Module::orderBy('code')->get();
        $programmes = Programme::orderBy('name')->get();
        
        // Build query with filters
        $query = DB::table('student_results')
            ->join('students', 'student_results.student_id', '=', 'students.id')
            ->join('modules', 'student_results.module_id', '=', 'modules.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('academic_years', 'student_results.academic_year_id', '=', 'academic_years.id')
            ->leftJoin('programmes', 'students.programme_id', '=', 'programmes.id')
            ->select(
                'student_results.*',
                'students.registration_number',
                'students.current_level',
                'students.programme_id',
                'users.first_name',
                'users.last_name',
                'modules.code as module_code',
                'modules.name as module_name',
                'modules.nta_level as module_nta_level',
                'modules.cw_weight',
                'modules.exam_weight',
                'modules.default_credits',
                'academic_years.name as academic_year_name',
                'programmes.name as programme_name',
                'programmes.program_category'
            )
            ->orderBy('student_results.id', 'desc');
        
        // Apply filters
        if ($request->get('programme_id')) {
            $query->where('students.programme_id', $request->get('programme_id'));
        }
        if ($request->get('nta_level')) {
            $query->where('modules.nta_level', $request->get('nta_level'));
        }
        if ($request->get('academic_year_id')) {
            $query->where('student_results.academic_year_id', $request->get('academic_year_id'));
        }
        if ($request->get('semester')) {
            $query->where('student_results.semester', $request->get('semester'));
        }
        if ($request->get('search')) {
            $query->where('students.registration_number', 'LIKE', '%'.$request->get('search').'%');
        }
        
        $allResults = $query->get();
        
        return view('superadmin.results.index', compact('academicYears', 'allModules', 'programmes', 'allResults'));
    }
    
    /**
     * Show create result form
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $programmes = Programme::orderBy('name')->get();
        $allModules = Module::orderBy('code')->get();
        
        return view('superadmin.results.create', compact('academicYears', 'programmes', 'allModules'));
    }
    
    /**
     * Show edit result form
     */
    public function edit($id)
    {
        $result = StudentResult::findOrFail($id);
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $programmes = Programme::orderBy('name')->get();
        $allModules = Module::orderBy('code')->get();
        $students = Student::with('user')->orderBy('registration_number')->get();
        
        return view('superadmin.results.edit', compact('result', 'academicYears', 'programmes', 'allModules', 'students'));
    }
    
    /**
     * Show transcript view for a student
     */
    public function transcriptView($studentId)
    {
        $student = Student::with(['user', 'programme'])->find($studentId);
        
        if (!$student) {
            return redirect()->route('superadmin.results.index')->with('error', 'Student not found');
        }
        
        // Get NTA level from student's first module result
        $ntaLevel = 4;
        $firstResult = StudentResult::with(['module'])->where('student_id', $studentId)->first();
        if ($firstResult && $firstResult->module) {
            $ntaLevel = $firstResult->module->nta_level ?? 4;
        }
        
        // Get programme category
        $programCategory = $student->programme->program_category ?? 'non_health';
        
        // Get all results for this student
        $results = StudentResult::with(['module', 'academicYear'])
            ->where('student_id', $studentId)
            ->orderBy('academic_year_id')
            ->orderBy('semester')
            ->get();
        
        // Group by academic year and semester
        $grouped = [];
        
        foreach ($results as $result) {
            $yearId = $result->academic_year_id;
            $yearName = $result->academicYear->name ?? 'Unknown Year';
            $semester = $result->semester;
            $totalScore = ($result->raw_cw + $result->raw_exam);
            
            $moduleData = [
                'module_code' => $result->module->code ?? 'N/A',
                'module_name' => $result->module->name ?? 'N/A',
                'type' => $result->module->type ?? 'Core',
                'cw_score' => $result->raw_cw,
                'exam_score' => $result->raw_exam,
                'total_score' => $totalScore,
                'credits' => $result->module->default_credits ?? 3,
                'grade' => $result->grade,
                'grade_point' => $result->grade_point,
                'remark' => $result->remark
            ];
            
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
                $grouped[$yearId]['semester_1'][] = $moduleData;
            } else {
                $grouped[$yearId]['semester_2'][] = $moduleData;
            }
        }
        
        // Calculate GPAs
        foreach ($grouped as &$year) {
            $year['gpa_semester_1'] = $this->calculateGPAFromModules($year['semester_1']);
            $year['gpa_semester_2'] = $this->calculateGPAFromModules($year['semester_2']);
            
            $allModules = array_merge($year['semester_1'], $year['semester_2']);
            $year['year_gpa'] = $this->calculateGPAFromModules($allModules);
        }
        
        // Sort by year
        ksort($grouped);
        
        // Calculate overall CGPA
        $allModules = [];
        foreach ($grouped as $year) {
            $allModules = array_merge($allModules, $year['semester_1'], $year['semester_2']);
        }
        
        $cgpaData = $this->calculateGPAFromModules($allModules);
        $cgpa = is_array($cgpaData) ? ($cgpaData['gpa'] ?? 0) : $cgpaData;
        
        $studentName = '';
        if ($student->user) {
            $nameParts = array_filter([
                $student->user->first_name ?? '',
                $student->user->middle_name ?? '',
                $student->user->last_name ?? '',
                $student->user->surname ?? ''
            ]);
            $studentName = implode(' ', $nameParts);
        }
        
        return view('superadmin.results.transcript', [
            'student' => [
                'name' => $studentName ?: 'N/A',
                'registration_number' => $student->registration_number,
                'nacte_reg_number' => $student->nacte_reg_number,
                'programme' => $student->programme->name ?? 'N/A'
            ],
            'results' => array_values($grouped),
            'cgpa' => $cgpa,
            'total_credits' => is_array($cgpaData) ? ($cgpaData['total_credits'] ?? 0) : 0,
            'total_points' => is_array($cgpaData) ? ($cgpaData['total_points'] ?? 0) : 0,
            'classification' => $this->getClassification($cgpa, $ntaLevel),
            'nta_level' => $ntaLevel,
            'program_category' => $programCategory
        ]);
    }
    
    /**
     * Calculate GPA from modules array
     */
    private function calculateGPAFromModules($modules)
    {
        if (empty($modules) || !is_array($modules)) {
            return [
                'gpa' => 0,
                'total_credits' => 0,
                'total_points' => 0
            ];
        }
        
        $totalPoints = 0;
        $totalCredits = 0;
        
        foreach ($modules as $module) {
            if (is_array($module)) {
                $gradePoint = $module['grade_point'] ?? 0;
                $credits = $module['credits'] ?? 0;
                $totalPoints += $gradePoint * $credits;
                $totalCredits += $credits;
            }
        }
        
        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;
        
        return [
            'gpa' => $gpa,
            'total_credits' => $totalCredits,
            'total_points' => round($totalPoints, 2)
        ];
    }
    
    /**
     * Get classification based on GPA and NTA Level
     * NTA Level 6 - 5.0 Scale
     * NTA Level 4 & 5 - 4.0 Scale
     */
    private function getClassification($gpa, $ntaLevel = 4)
    {
        // NTA LEVEL 6 - 5.0 SCALE
        if ($ntaLevel == 6) {
            if ($gpa >= 4.5) return 'First Class Honours';
            if ($gpa >= 3.5) return 'Second Class Honours (Upper)';
            if ($gpa >= 2.5) return 'Second Class Honours (Lower)';
            if ($gpa >= 2.0) return 'Pass';
            return 'Fail';
        }
        
        // NTA LEVEL 4 & 5 - 4.0 SCALE
        if ($gpa >= 4.5) return 'First Class Honours';
        if ($gpa >= 3.5) return 'Second Class Honours (Upper)';
        if ($gpa >= 2.5) return 'Second Class Honours (Lower)';
        if ($gpa >= 2.0) return 'Pass';
        return 'Fail';
    }
    
    /**
     * API: Get grade from grading_systems table
     */
    private function getGradeFromDatabase($totalScore, $ntaLevel, $programCategory)
    {
        $grading = GradingSystem::where('nta_level', $ntaLevel)
            ->where('program_category', $programCategory)
            ->where('is_active', 1)
            ->where('min_score', '<=', $totalScore)
            ->where('max_score', '>=', $totalScore)
            ->first();
        
        if (!$grading) {
            $grading = GradingSystem::where('nta_level', $ntaLevel)
                ->where('program_category', 'all')
                ->where('is_active', 1)
                ->where('min_score', '<=', $totalScore)
                ->where('max_score', '>=', $totalScore)
                ->first();
        }
        
        if ($grading) {
            return [
                'grade' => $grading->grade,
                'grade_point' => (float) $grading->grade_point
            ];
        }
        
        return ['grade' => 'F', 'grade_point' => 0.00];
    }
    
    /**
     * API: Get module weights
     */
    public function getModuleWeights($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'cw_weight' => $module->cw_weight ?? 50,
                'exam_weight' => $module->exam_weight ?? 50,
                'nta_level' => $module->nta_level,
                'default_credits' => $module->default_credits ?? 3,
                'pass_mark' => $module->pass_mark ?? 50
            ]
        ]);
    }
    
    /**
     * API: Search students
     */
    public function searchStudents(Request $request)
    {
        $search = $request->search;
        $programmeId = $request->programme_id;
        $ntaLevel = $request->nta_level;
        
        $query = Student::with(['user', 'programme'])
            ->where('status', 'active');
        
        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }
        
        if ($ntaLevel) {
            $query->where('current_level', $ntaLevel);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'LIKE', "%{$search}%")
                  ->orWhere('nacte_reg_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'LIKE', "%{$search}%")
                         ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        $students = $query->orderBy('registration_number')->paginate(20);
        
        $students->getCollection()->transform(function($s) {
            $name = '';
            if ($s->user) {
                $name = trim(($s->user->first_name ?? '') . ' ' . ($s->user->last_name ?? ''));
            }
            return [
                'id' => $s->id,
                'registration_number' => $s->registration_number,
                'nacte_reg_number' => $s->nacte_reg_number,
                'name' => $name ?: 'N/A',
                'programme_name' => $s->programme->name ?? 'N/A',
                'programme_category' => $s->programme->program_category ?? 'standard',
                'current_level' => $s->current_level
            ];
        });
        
        return response()->json(['success' => true, 'data' => $students]);
    }
    
    /**
     * API: Search modules for specific programme and level
     */
    public function searchModules(Request $request)
    {
        $programmeId = $request->programme_id;
        $ntaLevel = $request->nta_level;
        $search = $request->search;
        
        if (!$programmeId || !$ntaLevel) {
            return response()->json(['success' => false, 'message' => 'Programme and NTA Level required'], 422);
        }
        
        $curriculumModuleIds = DB::table('curriculum')
            ->where('programme_id', $programmeId)
            ->pluck('module_id')
            ->toArray();
        
        if (empty($curriculumModuleIds)) {
            return response()->json(['success' => true, 'data' => ['data' => [], 'total' => 0]]);
        }
        
        $query = Module::whereIn('id', $curriculumModuleIds)
            ->where('is_active', 1)
            ->where('nta_level', $ntaLevel);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }
        
        $modules = $query->orderBy('code')->get();
        
        $modules = $modules->map(function($module) {
            return [
                'id' => $module->id,
                'code' => $module->code,
                'name' => $module->name,
                'cw_weight' => $module->cw_weight ?? 50,
                'exam_weight' => $module->exam_weight ?? 50,
                'nta_level' => $module->nta_level,
                'type' => $module->type,
                'default_credits' => $module->default_credits ?? 3,
                'pass_mark' => $module->pass_mark ?? 50
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $modules,
                'total' => $modules->count()
            ]
        ]);
    }
    
    /**
     * API: Calculate score preview
     */
    public function calculateScore(Request $request)
    {
        $moduleId = $request->module_id;
        $cw = (float) $request->cw;
        $exam = (float) $request->exam;
        $studentId = $request->student_id;
        
        $module = Module::find($moduleId);
        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }
        
        $programCategory = 'all';
        if ($studentId) {
            $student = Student::with('programme')->find($studentId);
            if ($student && $student->programme) {
                $programCategory = $student->programme->program_category ?? 'all';
            }
        }
        
        $totalScore = $cw + $exam;
        $ntaLevel = $module->nta_level;
        $gradeData = $this->getGradeFromDatabase($totalScore, $ntaLevel, $programCategory);
        $passMark = $module->pass_mark ?? 50;
        $resultStatus = $totalScore >= $passMark ? 'pass' : 'fail';
        $cwWeight = $module->cw_weight ?? 50;
        $examWeight = $module->exam_weight ?? 50;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalScore,
                'grade' => $gradeData['grade'],
                'grade_point' => $gradeData['grade_point'],
                'pass_mark' => $passMark,
                'result_status' => $resultStatus,
                'cw_weight' => $cwWeight,
                'exam_weight' => $examWeight,
                'nta_level' => $ntaLevel,
                'program_category' => $programCategory,
                'credits' => $module->default_credits ?? 3
            ]
        ]);
    }
    
    /**
     * API: Get student by registration number
     */
    public function getStudentByRegNumber(Request $request)
    {
        $student = Student::with(['user', 'programme'])
            ->where('registration_number', $request->reg_number)
            ->orWhere('nacte_reg_number', $request->reg_number)
            ->first();
        
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }
        
        $name = '';
        if ($student->user) {
            $name = trim(($student->user->first_name ?? '') . ' ' . ($student->user->last_name ?? ''));
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'registration_number' => $student->registration_number,
                'nacte_reg_number' => $student->nacte_reg_number,
                'name' => $name ?: 'N/A',
                'programme_id' => $student->programme_id,
                'programme_name' => $student->programme->name ?? 'N/A',
                'programme_category' => $student->programme->program_category ?? 'all',
                'current_level' => $student->current_level
            ]
        ]);
    }
    
    /**
     * API: Get results list
     */
    public function getList(Request $request)
    {
        $query = StudentResult::with(['student.user', 'student.programme', 'module', 'academicYear']);
        
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->module_id) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->semester) {
            $query->where('semester', $request->semester);
        }
        
        $results = $query->orderBy('id', 'desc')->paginate(100);
        
        return response()->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * API: Get single result
     */
    public function show($id)
    {
        $result = StudentResult::with(['student', 'module'])->find($id);
        return response()->json(['success' => true, 'data' => $result]);
    }
    
    /**
     * API: Store result
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'module_id' => 'required|exists:modules,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|in:1,2',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $student = Student::with('programme')->find($request->student_id);
        $module = Module::find($request->module_id);
        
        if (!$student || !$module) {
            return response()->json(['success' => false, 'message' => 'Invalid student or module'], 422);
        }
        
        // Verify module belongs to student's programme
        $moduleBelongsToProgramme = DB::table('curriculum')
            ->where('programme_id', $student->programme_id)
            ->where('module_id', $module->id)
            ->exists();
        
        if (!$moduleBelongsToProgramme) {
            return response()->json([
                'success' => false, 
                'message' => 'This module is not assigned to the student\'s programme.'
            ], 422);
        }
        
        $cw = (float) $request->raw_cw;
        $exam = (float) $request->raw_exam;
        $totalScore = $cw + $exam;
        
        $programCategory = $student->programme->program_category ?? 'all';
        $ntaLevel = $module->nta_level;
        
        // Validate scores based on module weights
        $cwWeight = $module->cw_weight ?? 50;
        $examWeight = $module->exam_weight ?? 50;
        
        if ($cw > $cwWeight) {
            return response()->json([
                'success' => false,
                'message' => "CW score cannot exceed {$cwWeight}% for this module"
            ], 422);
        }
        if ($exam > $examWeight) {
            return response()->json([
                'success' => false,
                'message' => "Exam score cannot exceed {$examWeight}% for this module"
            ], 422);
        }
        
        $gradeData = $this->getGradeFromDatabase($totalScore, $ntaLevel, $programCategory);
        $passMark = $module->pass_mark ?? 50;
        $resultStatus = $totalScore >= $passMark ? 'pass' : 'fail';
        $remark = $totalScore >= $passMark ? 'Pass' : 'Fail';
        
        $studentResult = StudentResult::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'module_id' => $request->module_id,
                'academic_year_id' => $request->academic_year_id,
                'semester' => $request->semester,
            ],
            [
                'raw_cw' => $cw,
                'raw_exam' => $exam,
                'weighted_score' => $totalScore,
                'grade' => $gradeData['grade'],
                'grade_point' => $gradeData['grade_point'],
                'remark' => $remark,
                'result_status' => $resultStatus,
                'workflow_status' => $request->workflow_status ?? 'draft',
                'attempt_type' => $request->attempt_type ?? 'normal',
                'attempt_no' => $request->attempt_no ?? 1,
            ]
        );
        
        return response()->json([
            'success' => true, 
            'message' => 'Result saved successfully', 
            'data' => $studentResult
        ]);
    }
    
    /**
     * API: Update result
     */
    public function update(Request $request, $id)
    {
        $result = StudentResult::find($id);
        if (!$result) {
            return response()->json(['success' => false, 'message' => 'Result not found'], 404);
        }
        
        $student = Student::with('programme')->find($result->student_id);
        $module = Module::find($result->module_id);
        
        $cw = (float) $request->raw_cw;
        $exam = (float) $request->raw_exam;
        $totalScore = $cw + $exam;
        
        $programCategory = $student->programme->program_category ?? 'all';
        $ntaLevel = $module->nta_level;
        
        $cwWeight = $module->cw_weight ?? 50;
        $examWeight = $module->exam_weight ?? 50;
        
        if ($cw > $cwWeight || $exam > $examWeight) {
            return response()->json([
                'success' => false,
                'message' => 'Scores exceed maximum allowed for this module'
            ], 422);
        }
        
        $gradeData = $this->getGradeFromDatabase($totalScore, $ntaLevel, $programCategory);
        $passMark = $module->pass_mark ?? 50;
        $resultStatus = $totalScore >= $passMark ? 'pass' : 'fail';
        $remark = $totalScore >= $passMark ? 'Pass' : 'Fail';
        
        $result->update([
            'raw_cw' => $cw,
            'raw_exam' => $exam,
            'weighted_score' => $totalScore,
            'grade' => $gradeData['grade'],
            'grade_point' => $gradeData['grade_point'],
            'remark' => $remark,
            'result_status' => $resultStatus,
            'semester' => $request->semester,
            'attempt_type' => $request->attempt_type,
            'attempt_no' => $request->attempt_no,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Result updated']);
    }
    
    /**
     * API: Delete result
     */
    public function destroy($id)
    {
        $result = StudentResult::find($id);
        if ($result) {
            $result->delete();
            return response()->json(['success' => true, 'message' => 'Result deleted']);
        }
        return response()->json(['success' => false, 'message' => 'Result not found'], 404);
    }
    
    /**
     * API: Get dashboard stats
     */
    public function getDashboardStats()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => StudentResult::count(),
                'draft' => StudentResult::where('workflow_status', 'draft')->count(),
                'approved' => StudentResult::where('workflow_status', 'approved')->count(),
                'published' => StudentResult::where('workflow_status', 'published')->count(),
            ]
        ]);
    }
}