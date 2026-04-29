<?php
// app/Http/Controllers/StudentResultController.php

namespace App\Http\Controllers;

use App\Services\StudentResultService;
use App\Models\Module;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StudentResultController extends Controller
{
    protected $resultService;
    
    public function __construct(StudentResultService $resultService)
    {
        $this->resultService = $resultService;
        $this->middleware('auth');
    }
    
    /**
     * GET /api/student-results
     * Display list of results
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $filters = [
            'module_id' => $request->get('module_id'),
            'student_id' => $request->get('student_id'),
            'academic_year_id' => $request->get('academic_year_id', $this->getCurrentAcademicYearId()),
            'semester' => $request->get('semester'),
            'workflow_status' => $request->get('workflow_status'),
            'per_page' => $request->get('per_page', 50)
        ];
        
        $results = $this->resultService->getResults($filters, $user);
        
        // Get filter options based on role
        $filterOptions = $this->getFilterOptions($user, $filters['academic_year_id']);
        
        return response()->json([
            'success' => true,
            'data' => $results,
            'filters' => $filterOptions,
            'user_role' => $user->getRoleNames()->first()
        ]);
    }
    
    /**
     * GET /api/student-results/{id}
     * Display single result
     */
    public function show($id)
    {
        $result = $this->resultService->getResult($id);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found'
            ], 404);
        }
        
        // Authorization check
        if (!$this->canAccessResult($result)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this result'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    
    /**
     * POST /api/student-results
     * Store new result
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'module_id' => 'required|exists:modules,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|integer|in:1,2',
            'raw_cw' => 'nullable|numeric|min:0|max:100',
            'raw_exam' => 'nullable|numeric|min:0|max:100',
            'attempt_type' => 'sometimes|in:normal,supplementary,special,carryover',
            'attempt_no' => 'sometimes|integer|min:1',
            'workflow_status' => 'sometimes|in:draft,approved,published'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Authorization check
        if (!$this->canManageModule($request->module_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to add results for this module'
            ], 403);
        }
        
        $result = $this->resultService->storeOrUpdateResult($request->all());
        
        if ($result['success']) {
            return response()->json($result, 201);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
    
    /**
     * PUT /api/student-results/{id}
     * Update existing result
     */
    public function update(Request $request, $id)
    {
        $existingResult = $this->resultService->getResult($id);
        
        if (!$existingResult) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found'
            ], 404);
        }
        
        // Authorization check
        if (!$this->canManageModule($existingResult->module_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this result'
            ], 403);
        }
        
        // Check if result is locked (approved/published)
        if (in_array($existingResult->workflow_status, ['approved', 'published'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update approved or published results'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'raw_cw' => 'nullable|numeric|min:0|max:100',
            'raw_exam' => 'nullable|numeric|min:0|max:100',
            'attempt_type' => 'sometimes|in:normal,supplementary,special,carryover',
            'attempt_no' => 'sometimes|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $request->all();
        $data['student_id'] = $existingResult->student_id;
        $data['module_id'] = $existingResult->module_id;
        $data['academic_year_id'] = $existingResult->academic_year_id;
        $data['semester'] = $existingResult->semester;
        
        $result = $this->resultService->storeOrUpdateResult($data);
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
    
    /**
     * DELETE /api/student-results/{id}
     * Delete result (Super Admin only)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can delete results'
            ], 403);
        }
        
        $result = $this->resultService->deleteResult($id);
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
    
    /**
     * POST /api/student-results/bulk-upload
     * Bulk upload results from Excel
     */
    public function bulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|integer|in:1,2',
            'module_id' => 'nullable|exists:modules,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        $file = $request->file('file');
        $academicYearId = $request->academic_year_id;
        $semester = $request->semester;
        $moduleId = $request->module_id;
        
        // For tutors, module_id is required
        if ($user->hasRole('tutor') && !$moduleId) {
            return response()->json([
                'success' => false,
                'message' => 'Module ID is required for tutors'
            ], 422);
        }
        
        // Authorization for tutors
        if ($user->hasRole('tutor') && $moduleId) {
            if (!$this->canManageModule($moduleId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to upload for this module'
                ], 403);
            }
        }
        
        $result = $this->resultService->bulkUploadResults(
            $file, $user->id, $academicYearId, $semester, $moduleId
        );
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
    
    /**
     * PATCH /api/student-results/{id}/workflow-status
     * Update workflow status (draft/approved/published)
     */
    public function updateWorkflowStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,approved,published'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->resultService->getResult($id);
        
        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Result not found'
            ], 404);
        }
        
        // Authorization based on status change
        $user = Auth::user();
        $status = $request->status;
        
        if ($status === 'approved' && !$user->hasRole('hod') && !$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOD or Super Admin can approve results'
            ], 403);
        }
        
        if ($status === 'published' && !$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Super Admin can publish results'
            ], 403);
        }
        
        $updateResult = $this->resultService->updateWorkflowStatus($id, $status, $user->id);
        
        if ($updateResult['success']) {
            return response()->json($updateResult);
        }
        
        return response()->json([
            'success' => false,
            'message' => $updateResult['message']
        ], 500);
    }
    
    /**
     * POST /api/student-results/batches/{batchId}/approve
     * Approve entire batch
     */
    public function approveBatch($batchId)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('hod') && !$user->hasRole('super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOD or Super Admin can approve batches'
            ], 403);
        }
        
        $result = $this->resultService->bulkApproveResults($batchId, $user->id);
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 500);
    }
    
    /**
     * GET /api/student-results/transcript/{studentId}
     * Get student transcript
     */
    public function transcript($studentId, Request $request)
    {
        $user = Auth::user();
        
        // Student can view own transcript
        $studentUserId = \App\Models\Student::find($studentId)?->user_id;
        
        if ($studentUserId !== $user->id && !$user->hasRole('super_admin') && !$user->hasRole('hod')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this transcript'
            ], 403);
        }
        
        $academicYearId = $request->get('academic_year_id');
        $transcript = $this->resultService->getStudentTranscript($studentId, $academicYearId);
        
        return response()->json([
            'success' => true,
            'data' => $transcript
        ]);
    }
    
    /**
     * GET /api/student-results/filter-options
     * Get filter options for dropdowns
     */
    public function filterOptions(Request $request)
    {
        $user = Auth::user();
        $academicYearId = $request->get('academic_year_id', $this->getCurrentAcademicYearId());
        
        $options = $this->getFilterOptions($user, $academicYearId);
        
        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }
    
    // ============ PRIVATE HELPER METHODS ============
    
    /**
     * Get current academic year ID
     */
    private function getCurrentAcademicYearId()
    {
        $currentYear = AcademicYear::where('status', 'active')
            ->where('is_active', true)
            ->first();
            
        return $currentYear ? $currentYear->id : null;
    }
    
    /**
     * Get filter options based on user role
     */
    private function getFilterOptions($user, $academicYearId)
    {
        $query = Module::with('department');
        
        if ($user->hasRole('tutor')) {
            $query->whereHas('courseAllocations', function($q) use ($user, $academicYearId) {
                $q->where('user_id', $user->id);
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
            });
        } elseif ($user->hasRole('hod')) {
            $query->where('department_id', $user->department_id);
        }
        
        $modules = $query->orderBy('code')->get(['id', 'code', 'name']);
        
        $academicYears = AcademicYear::orderBy('id', 'desc')
            ->get(['id', 'name', 'is_locked']);
        
        $workflowStatuses = [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'approved', 'label' => 'Approved'],
            ['value' => 'published', 'label' => 'Published']
        ];
        
        return [
            'modules' => $modules,
            'academic_years' => $academicYears,
            'semesters' => [
                ['value' => 1, 'label' => 'Semester 1'],
                ['value' => 2, 'label' => 'Semester 2']
            ],
            'workflow_statuses' => $workflowStatuses
        ];
    }
    
    /**
     * Check if user can manage a specific module
     */
    private function canManageModule($moduleId)
    {
        $user = Auth::user();
        
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        $module = Module::find($moduleId);
        if (!$module) {
            return false;
        }
        
        if ($user->hasRole('hod')) {
            return $module->department_id === $user->department_id;
        }
        
        if ($user->hasRole('tutor')) {
            return Module::where('id', $moduleId)
                ->whereHas('courseAllocations', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->exists();
        }
        
        return false;
    }
    
    /**
     * Check if user can access a result
     */
    private function canAccessResult($result)
    {
        $user = Auth::user();
        
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('hod')) {
            return $result->module->department_id === $user->department_id;
        }
        
        if ($user->hasRole('tutor')) {
            return Module::where('id', $result->module_id)
                ->whereHas('courseAllocations', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->exists();
        }
        
        // Student can view own results
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if ($student && $student->id === $result->student_id) {
            return true;
        }
        
        return false;
    }
}