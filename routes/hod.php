<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HodController;

// =====================
// HEAD OF DEPARTMENT ROUTES
// =====================
Route::middleware(['auth', 'role:Head_of_Department'])
    ->prefix('hod')
    ->name('hod.')
    ->group(function () {
        
        // =====================
        // DASHBOARD ROUTES
        // =====================
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/', [HodController::class, 'dashboard'])->name('index');
            Route::get('/enrollment-summary', [HodController::class, 'enrollmentSummary'])->name('enrollment-summary');
            Route::get('/financial-snapshot', [HodController::class, 'financialSnapshot'])->name('financial-snapshot');
            Route::get('/result-status', [HodController::class, 'resultStatus'])->name('result-status');
            Route::get('/pending-approvals', [HodController::class, 'pendingApprovals'])->name('pending-approvals');
        });
        
// =====================
// STUDENTS MANAGEMENT
// =====================
Route::prefix('students')->name('students.')->group(function () {
    Route::get('/', [HodController::class, 'students'])->name('all');
    Route::get('/active', [HodController::class, 'activeStudents'])->name('active');
    Route::get('/deferred', [HodController::class, 'deferredStudents'])->name('deferred');
    Route::get('/alumni', [HodController::class, 'alumni'])->name('alumni');
    Route::get('/profile/{id}', [HodController::class, 'studentDetails'])->name('profile');
    Route::get('/academic-history/{id}', [HodController::class, 'academicHistory'])->name('academic-history');
    Route::get('/register-courses/{id}', [HodController::class, 'registerCoursesForm'])->name('register-courses');
    Route::post('/register-courses/{id}', [HodController::class, 'storeCourseRegistration'])->name('store-course-registration');
    Route::get('/clearance/{id}', [HodController::class, 'clearanceStatus'])->name('clearance');
    Route::post('/clearance/{id}/{item}', [HodController::class, 'updateClearance'])->name('update-clearance');
    
    // ADD THIS ROUTE FOR ACTIVATING STUDENTS
    Route::post('/activate/{id}', [HodController::class, 'activateStudent'])->name('activate');
});

        // =====================
        // PROMOTION ROUTES
        // =====================
       // Promotion Routes
Route::prefix('promotion')->name('promotion.')->group(function () {
    Route::get('/semester', [App\Http\Controllers\Hod\PromotionController::class, 'semesterPromotion'])->name('semester');
    Route::post('/semester', [App\Http\Controllers\Hod\PromotionController::class, 'processSemesterPromotion'])->name('process-semester');
    Route::get('/year', [App\Http\Controllers\Hod\PromotionController::class, 'yearPromotion'])->name('year');
    Route::post('/year', [App\Http\Controllers\Hod\PromotionController::class, 'processYearPromotion'])->name('process-year');
    Route::get('/bulk', [App\Http\Controllers\Hod\PromotionController::class, 'bulkPromotion'])->name('bulk');
    Route::post('/bulk', [App\Http\Controllers\Hod\PromotionController::class, 'processBulkPromotion'])->name('process-bulk');
    Route::get('/history', [App\Http\Controllers\Hod\PromotionController::class, 'promotionHistory'])->name('history');
    Route::get('/results', [App\Http\Controllers\Hod\PromotionController::class, 'promotionResults'])->name('results');
});

        // =====================
        // ACADEMIC MANAGEMENT
        // =====================
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::get('/programs', [HodController::class, 'programs'])->name('programs');
            Route::get('/courses', [HodController::class, 'courses'])->name('courses');
            Route::get('/curriculum', [HodController::class, 'curriculum'])->name('curriculum');
            Route::get('/semester-setup', [HodController::class, 'semesterSetup'])->name('semester-setup');
            Route::post('/semester-setup', [HodController::class, 'storeSemesterSetup'])->name('store-semester-setup');
            Route::get('/academic-years', [HodController::class, 'academicYears'])->name('years');
            Route::get('/class-allocation', [HodController::class, 'classAllocation'])->name('class-allocation');
            Route::post('/class-allocation', [HodController::class, 'storeClassAllocation'])->name('store-class-allocation');
            Route::get('/timetable', [HodController::class, 'timetable'])->name('timetable');
            Route::get('/exam-setup', [HodController::class, 'examSetup'])->name('exam-setup');
            Route::post('/exam-setup', [HodController::class, 'storeExamSetup'])->name('store-exam-setup');
        });

        // =====================
// RESULTS MANAGEMENT
// =====================
Route::prefix('results')->name('results.')->group(function () {
    Route::get('/enter', [HodController::class, 'enterResults'])->name('enter');
    Route::post('/enter', [HodController::class, 'storeResults'])->name('store');
    Route::get('/moderate', [HodController::class, 'moderateResults'])->name('moderate');
    Route::post('/moderate/{id}', [HodController::class, 'processModeration'])->name('process-moderation');
    Route::get('/approve', [HodController::class, 'approveResults'])->name('approve');
    Route::post('/approve/{id}', [HodController::class, 'approveResult'])->name('approve-single');
    Route::post('/approve-bulk', [HodController::class, 'bulkApproveResults'])->name('approve-bulk');
    Route::get('/publish', [HodController::class, 'publishResults'])->name('publish');
    Route::post('/publish', [HodController::class, 'processPublish'])->name('process-publish');
    Route::get('/gpa-report', [HodController::class, 'gpaReport'])->name('gpa-report');
    Route::get('/supplementary', [HodController::class, 'supplementaryList'])->name('supplementary');
    Route::get('/carry-over', [HodController::class, 'carryOverList'])->name('carry-over');
    Route::get('/transcript/{id}', [HodController::class, 'transcript'])->name('transcript');
    Route::get('/transcript/{id}/download', [HodController::class, 'downloadTranscript'])->name('transcript-download');

    // =====================
    // RESULT APPROVAL (HOD)
    // =====================
    Route::get('/pending', [\App\Http\Controllers\Hod\ResultApprovalController::class, 'index'])->name('pending');
    Route::post('/approve/{result}', [\App\Http\Controllers\Hod\ResultApprovalController::class, 'approve'])->name('approve-result');
    Route::post('/reject/{result}', [\App\Http\Controllers\Hod\ResultApprovalController::class, 'reject'])->name('reject-result');
});

        // =====================
        // FINANCE ROUTES
        // =====================
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/fee-status', [HodController::class, 'feeStatus'])->name('fee-status');
            Route::get('/outstanding', [HodController::class, 'outstandingBalances'])->name('outstanding');
            Route::get('/clearance', [HodController::class, 'financialClearance'])->name('clearance');
            Route::get('/sponsorship', [HodController::class, 'sponsorshipList'])->name('sponsorship');
            Route::get('/budget', [HodController::class, 'departmentBudget'])->name('budget');
            Route::post('/budget', [HodController::class, 'storeBudget'])->name('store-budget');
            Route::get('/expenditure', [HodController::class, 'expenditureTracking'])->name('expenditure');
            Route::get('/requisition-approval', [HodController::class, 'requisitionApproval'])->name('requisition-approval');
            Route::post('/requisition-approve/{id}', [HodController::class, 'approveRequisition'])->name('approve-requisition');
        });

        // =====================
        // STAFF MANAGEMENT
        // =====================
        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [HodController::class, 'staffList'])->name('list');
            Route::get('/lecturers', [HodController::class, 'lecturers'])->name('lecturers');
            Route::get('/teaching-load', [HodController::class, 'teachingLoad'])->name('teaching-load');
            Route::get('/course-allocation', [HodController::class, 'courseAllocation'])->name('course-allocation');
            Route::post('/course-allocation', [HodController::class, 'storeCourseAllocation'])->name('store-course-allocation');
            Route::get('/leave-requests', [HodController::class, 'leaveRequests'])->name('leave-requests');
            Route::post('/leave-approve/{id}', [HodController::class, 'approveLeave'])->name('approve-leave');
            Route::post('/leave-reject/{id}', [HodController::class, 'rejectLeave'])->name('reject-leave');
            Route::get('/performance', [HodController::class, 'staffPerformance'])->name('performance');
            Route::get('/details/{id}', [HodController::class, 'staffDetails'])->name('details');
        });

        // =====================
        // RESEARCH & FIELD
        // =====================
        Route::prefix('field')->name('field.')->group(function () {
            Route::get('/placement', [HodController::class, 'fieldPlacement'])->name('placement');
            Route::post('/placement', [HodController::class, 'storePlacement'])->name('store-placement');
            Route::get('/clinical-rotation', [HodController::class, 'clinicalRotation'])->name('clinical-rotation');
            Route::post('/clinical-rotation', [HodController::class, 'storeRotation'])->name('store-rotation');
            Route::get('/community-tracking', [HodController::class, 'communityTracking'])->name('community-tracking');
            Route::get('/logbook', [HodController::class, 'logbookTracking'])->name('logbook');
            Route::get('/logbook/{id}', [HodController::class, 'viewLogbook'])->name('view-logbook');
            Route::get('/supervisor-allocation', [HodController::class, 'supervisorAllocation'])->name('supervisor-allocation');
            Route::post('/supervisor-allocation', [HodController::class, 'storeSupervisorAllocation'])->name('store-supervisor');
            Route::get('/practical-assessment', [HodController::class, 'practicalAssessment'])->name('practical-assessment');
            Route::post('/practical-assessment', [HodController::class, 'storePracticalAssessment'])->name('store-practical');
        });

        // =====================
        // REPORTS
        // =====================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/enrollment', [HodController::class, 'enrollmentReport'])->name('enrollment');
            Route::get('/enrollment/export', [HodController::class, 'exportEnrollmentReport'])->name('enrollment-export');
            Route::get('/performance', [HodController::class, 'performanceReport'])->name('performance');
            Route::get('/performance/export', [HodController::class, 'exportPerformanceReport'])->name('performance-export');
            Route::get('/graduation', [HodController::class, 'graduationList'])->name('graduation');
            Route::get('/graduation/export', [HodController::class, 'exportGraduationList'])->name('graduation-export');
            Route::get('/promotion', [HodController::class, 'promotionList'])->name('promotion');
            Route::get('/promotion/export', [HodController::class, 'exportPromotionList'])->name('promotion-export');
            Route::get('/financial', [HodController::class, 'financialSummary'])->name('financial');
            Route::get('/financial/export', [HodController::class, 'exportFinancialSummary'])->name('financial-export');
            Route::get('/staff-workload', [HodController::class, 'staffWorkloadReport'])->name('staff-workload');
            Route::get('/staff-workload/export', [HodController::class, 'exportStaffWorkload'])->name('staff-workload-export');
            
            // Legacy routes (for backward compatibility)
            Route::get('/progress', [HodController::class, 'progressReport'])->name('progress');
            Route::get('/attendance', [HodController::class, 'attendanceReport'])->name('attendance');
        });

        // =====================
        // APPROVALS
        // =====================
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/results', [HodController::class, 'pendingResultsApproval'])->name('results');
            Route::get('/promotion', [HodController::class, 'pendingPromotionApproval'])->name('promotion');
            Route::get('/budget', [HodController::class, 'pendingBudgetApproval'])->name('budget');
            Route::post('/budget/{id}/approve', [HodController::class, 'approveBudget'])->name('approve-budget');
            Route::post('/budget/{id}/reject', [HodController::class, 'rejectBudget'])->name('reject-budget');
            Route::get('/leave', [HodController::class, 'pendingLeaveApproval'])->name('leave');
            Route::get('/clearance', [HodController::class, 'pendingClearanceApproval'])->name('clearance');
            Route::post('/clearance/{id}/approve', [HodController::class, 'approveClearance'])->name('approve-clearance');
        });

        // =====================
        // ASSETS & RESOURCES
        // =====================
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/department', [HodController::class, 'departmentAssets'])->name('department');
            Route::get('/lab-equipment', [HodController::class, 'labEquipment'])->name('lab-equipment');
            Route::get('/allocation', [HodController::class, 'assetAllocation'])->name('allocation');
            Route::post('/allocation', [HodController::class, 'storeAssetAllocation'])->name('store-allocation');
            Route::get('/maintenance', [HodController::class, 'maintenanceLog'])->name('maintenance');
            Route::post('/maintenance', [HodController::class, 'storeMaintenanceLog'])->name('store-maintenance');
        });

        // =====================
        // SETTINGS
        // =====================
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/department-profile', [HodController::class, 'departmentProfile'])->name('department-profile');
            Route::post('/department-profile', [HodController::class, 'updateDepartmentProfile'])->name('update-department');
            Route::get('/academic-calendar', [HodController::class, 'viewAcademicCalendar'])->name('academic-calendar');
            Route::get('/grading-system', [HodController::class, 'viewGradingSystem'])->name('grading-system');
            Route::get('/notifications', [HodController::class, 'notificationSettings'])->name('notifications');
            Route::post('/notifications', [HodController::class, 'updateNotificationSettings'])->name('update-notifications');
            Route::get('/profile', [HodController::class, 'profile'])->name('profile');
            Route::post('/profile', [HodController::class, 'updateProfile'])->name('update-profile');
        });

        // =====================
        // NOTIFICATIONS
        // =====================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [HodController::class, 'allNotifications'])->name('all');
            Route::post('/{id}/read', [HodController::class, 'markNotificationRead'])->name('mark-read');
            Route::post('/mark-all-read', [HodController::class, 'markAllNotificationsRead'])->name('mark-all-read');
        });

        // =====================
        // API/JSON ROUTES (for AJAX requests)
        // =====================
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/students/search', [HodController::class, 'apiSearchStudents'])->name('search-students');
            Route::get('/courses/{programmeId}', [HodController::class, 'apiGetCourses'])->name('get-courses');
            Route::get('/staff/{departmentId}', [HodController::class, 'apiGetStaff'])->name('get-staff');
            Route::get('/statistics', [HodController::class, 'apiStatistics'])->name('statistics');
        });

        // =====================
        // EXPORT ROUTES
        // =====================
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('/students', [HodController::class, 'exportStudents'])->name('students');
            Route::get('/results', [HodController::class, 'exportResults'])->name('results');
            Route::get('/attendance', [HodController::class, 'exportAttendance'])->name('attendance');
        });

        // =====================
        // LEGACY/COMPATIBILITY ROUTES
        // =====================
        // Keep these for backward compatibility with existing code
        Route::get('/dashboard', [HodController::class, 'dashboard'])->name('dashboard'); // Legacy
        Route::get('/students', [HodController::class, 'students'])->name('students.all'); // Legacy
        Route::get('/students/{id}', [HodController::class, 'studentDetails'])->name('students.details'); // Legacy
        Route::get('/staff', [HodController::class, 'staffList'])->name('staff'); // Legacy
        Route::get('/analytics', [HodController::class, 'analytics'])->name('analytics'); // Legacy
    });

    