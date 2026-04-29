<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdmin\ResultController;
use App\Http\Controllers\SuperAdmin\Config\GeneralSettingsController;
use App\Http\Controllers\SuperAdmin\Config\RolesController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\StudentController;
use App\Http\Controllers\SuperAdmin\StaffController;
use App\Http\Controllers\SuperAdmin\CourseController;
use App\Http\Controllers\SuperAdmin\ApplicationController;
use App\Http\Controllers\SuperAdmin\PaymentController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\NotificationController;
use App\Http\Controllers\SuperAdmin\ProfileController;
use App\Http\Controllers\SuperAdmin\ProgrammeController;
use App\Http\Controllers\SuperAdmin\ProgrammeFeeController;
use App\Http\Controllers\SuperAdmin\SupplementaryFeeController;
use App\Http\Controllers\SuperAdmin\RepeatModuleFeeController;
use App\Http\Controllers\SuperAdmin\HostelFeeController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\CurriculumController;

// =====================
// SUPER ADMIN MAIN ROUTES
// =====================
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:SuperAdmin'])->group(function () {
    // =====================
    // DASHBOARD & HOME
    // =====================
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');

    // =====================
    // USERS MANAGEMENT
    // =====================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::put('/{id}/status', [UserController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/export/export', [UserController::class, 'export'])->name('export');
        Route::get('/import', [UserController::class, 'showImport'])->name('import.show');
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::post('/bulk-actions', [UserController::class, 'bulkActions'])->name('bulk-actions');
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::get('/{id}/reset-password', [UserController::class, 'showResetPassword'])->name('reset-password.show');
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });

    

    // =====================
    // STUDENTS MANAGEMENT
    // =====================
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{id}', [StudentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/enroll', [StudentController::class, 'showEnroll'])->name('enroll.show');
        Route::post('/{id}/enroll', [StudentController::class, 'enroll'])->name('enroll');
        Route::get('/export', [StudentController::class, 'export'])->name('export');
        Route::get('/{id}/transcript', [StudentController::class, 'transcript'])->name('transcript');
        Route::get('/{id}/fees', [StudentController::class, 'fees'])->name('fees');
        
        // Student Supplementary & Repeat Module Routes
        Route::prefix('{id}')->name('')->group(function () {
            // Supplementary
            Route::prefix('supplementary')->name('supplementary.')->group(function () {
                Route::get('/', [StudentController::class, 'supplementaryFees'])->name('fees');
                Route::get('/apply', [StudentController::class, 'applySupplementary'])->name('apply');
                Route::post('/store', [StudentController::class, 'storeSupplementary'])->name('store');
                Route::get('/history', [StudentController::class, 'supplementaryHistory'])->name('history');
                Route::get('/invoice/{payment}', [StudentController::class, 'supplementaryInvoice'])->name('invoice');
            });
            
            // Repeat Module
            Route::prefix('repeat-module')->name('repeat-module.')->group(function () {
                Route::get('/', [StudentController::class, 'repeatModuleFees'])->name('fees');
                Route::get('/apply', [StudentController::class, 'applyRepeatModule'])->name('apply');
                Route::post('/store', [StudentController::class, 'storeRepeatModule'])->name('store');
                Route::get('/history', [StudentController::class, 'repeatModuleHistory'])->name('history');
                Route::get('/invoice/{payment}', [StudentController::class, 'repeatModuleInvoice'])->name('invoice');
            });
        });
    });

    // =====================
    // STAFF MANAGEMENT
    // =====================
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::get('/{id}', [StaffController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
        Route::get('/export', [StaffController::class, 'export'])->name('export');
        Route::post('/{id}/assign-role', [StaffController::class, 'assignRole'])->name('assign-role');
        Route::get('/{id}/attendance', [StaffController::class, 'attendance'])->name('attendance');
        Route::get('/{id}/performance', [StaffController::class, 'performance'])->name('performance');
    });

    // =====================
    // COURSES MANAGEMENT
    // =====================
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/create', [CourseController::class, 'create'])->name('create');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{id}', [CourseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CourseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CourseController::class, 'update'])->name('update');
        Route::delete('/{id}', [CourseController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/curriculum', [CourseController::class, 'curriculum'])->name('curriculum');
        Route::post('/{id}/curriculum', [CourseController::class, 'updateCurriculum'])->name('curriculum.update');
        Route::get('/{id}/students', [CourseController::class, 'students'])->name('students');
        Route::post('/{id}/assign-tutor', [CourseController::class, 'assignTutor'])->name('assign-tutor');
        Route::get('/export', [CourseController::class, 'export'])->name('export');
    });

    // =====================
    // APPLICATIONS MANAGEMENT
    // =====================
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [ApplicationController::class, 'index'])->name('index');
        Route::get('/pending', [ApplicationController::class, 'pending'])->name('pending');
        Route::get('/approved', [ApplicationController::class, 'approved'])->name('approved');
        Route::get('/rejected', [ApplicationController::class, 'rejected'])->name('rejected');
        Route::get('/{id}', [ApplicationController::class, 'show'])->name('show');
        Route::put('/{id}/status', [ApplicationController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/review', [ApplicationController::class, 'review'])->name('review');
        Route::post('/{id}/interview', [ApplicationController::class, 'scheduleInterview'])->name('interview');
        Route::get('/export', [ApplicationController::class, 'export'])->name('export');
        Route::post('/bulk-process', [ApplicationController::class, 'bulkProcess'])->name('bulk-process');
    });

    // =====================
    // PAYMENTS MANAGEMENT
    // =====================
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PaymentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
        Route::get('/pending', [PaymentController::class, 'pending'])->name('pending');
        Route::get('/verified', [PaymentController::class, 'verified'])->name('verified');
        Route::get('/rejected', [PaymentController::class, 'rejected'])->name('rejected');
        Route::put('/{id}/verify', [PaymentController::class, 'verify'])->name('verify');
        Route::put('/{id}/reject', [PaymentController::class, 'reject'])->name('reject');
        Route::get('/export', [PaymentController::class, 'export'])->name('export');
        Route::get('/reports', [PaymentController::class, 'reports'])->name('reports');
        
        // Supplementary Payments
        Route::prefix('supplementary')->name('supplementary.')->group(function () {
            Route::get('/', [PaymentController::class, 'supplementaryIndex'])->name('index');
            Route::get('/pending', [PaymentController::class, 'supplementaryPending'])->name('pending');
            Route::get('/verified', [PaymentController::class, 'supplementaryVerified'])->name('verified');
            Route::get('/rejected', [PaymentController::class, 'supplementaryRejected'])->name('rejected');
            Route::put('/{id}/verify', [PaymentController::class, 'verifySupplementary'])->name('verify');
            Route::put('/{id}/reject', [PaymentController::class, 'rejectSupplementary'])->name('reject');
            Route::get('/{id}/invoice', [PaymentController::class, 'supplementaryInvoice'])->name('invoice');
            Route::get('/export', [PaymentController::class, 'exportSupplementary'])->name('export');
        });
        
        // Repeat Module Payments
        Route::prefix('repeat-module')->name('repeat-module.')->group(function () {
            Route::get('/', [PaymentController::class, 'repeatModuleIndex'])->name('index');
            Route::get('/pending', [PaymentController::class, 'repeatModulePending'])->name('pending');
            Route::get('/verified', [PaymentController::class, 'repeatModuleVerified'])->name('verified');
            Route::get('/rejected', [PaymentController::class, 'repeatModuleRejected'])->name('rejected');
            Route::put('/{id}/verify', [PaymentController::class, 'verifyRepeatModule'])->name('verify');
            Route::put('/{id}/reject', [PaymentController::class, 'rejectRepeatModule'])->name('reject');
            Route::get('/{id}/invoice', [PaymentController::class, 'repeatModuleInvoice'])->name('invoice');
            Route::get('/export', [PaymentController::class, 'exportRepeatModule'])->name('export');
        });
    });

    // =====================
    // REPORTS & ANALYTICS
    // =====================
    Route::prefix('reports')->name('reports.')->group(function () {
        // Enrollment Reports
        Route::get('/enrollment', [ReportController::class, 'enrollment'])->name('enrollment');
        Route::get('/enrollment-by-course', [ReportController::class, 'enrollmentByCourse'])->name('enrollment.by-course');
        Route::get('/enrollment-by-gender', [ReportController::class, 'enrollmentByGender'])->name('enrollment.by-gender');
        
        // Financial Reports
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/fees-collection', [ReportController::class, 'feesCollection'])->name('fees.collection');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        
        // Academic Reports
        Route::get('/academic-performance', [ReportController::class, 'academicPerformance'])->name('academic.performance');
        Route::get('/examination-results', [ReportController::class, 'examinationResults'])->name('examination.results');
        
        // Staff Reports
        Route::get('/staff-performance', [ReportController::class, 'staffPerformance'])->name('staff.performance');
        Route::get('/staff-attendance', [ReportController::class, 'staffAttendance'])->name('staff.attendance');
        
        // System Reports
        Route::get('/system-usage', [ReportController::class, 'systemUsage'])->name('system.usage');
        Route::get('/audit-logs', [ReportController::class, 'auditLogs'])->name('audit.logs');
        
        // Export Reports
        Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
        Route::get('/export/{type}', [ReportController::class, 'exportReport'])->name('export');
        
        // Supplementary Reports
        Route::prefix('supplementary')->name('supplementary.')->group(function () {
            Route::get('/fees-collection', [ReportController::class, 'supplementaryFeesCollection'])->name('fees.collection');
            Route::get('/students', [ReportController::class, 'supplementaryStudents'])->name('students');
            Route::get('/courses', [ReportController::class, 'supplementaryCourses'])->name('courses');
            Route::get('/revenue', [ReportController::class, 'supplementaryRevenue'])->name('revenue');
            Route::get('/summary', [ReportController::class, 'supplementarySummary'])->name('summary');
            Route::get('/trends', [ReportController::class, 'supplementaryTrends'])->name('trends');
            Route::get('/export', [ReportController::class, 'exportSupplementary'])->name('export');
        });
        
        // Repeat Module Reports
        Route::prefix('repeat-module')->name('repeat-module.')->group(function () {
            Route::get('/fees-collection', [ReportController::class, 'repeatModuleFeesCollection'])->name('fees.collection');
            Route::get('/students', [ReportController::class, 'repeatModuleStudents'])->name('students');
            Route::get('/courses', [ReportController::class, 'repeatModuleCourses'])->name('courses');
            Route::get('/revenue', [ReportController::class, 'repeatModuleRevenue'])->name('revenue');
            Route::get('/summary', [ReportController::class, 'repeatModuleSummary'])->name('summary');
            Route::get('/trends', [ReportController::class, 'repeatModuleTrends'])->name('trends');
            Route::get('/export', [ReportController::class, 'exportRepeatModule'])->name('export');
        });
    });

    // =====================
    // SYSTEM CONFIGURATION
    // =====================
    Route::prefix('config')->name('config.')->group(function () {
        // General Settings
        Route::get('/general', [GeneralSettingsController::class, 'index'])->name('general');
        Route::put('/general/update', [GeneralSettingsController::class, 'update'])->name('general.update');
        Route::post('/general/upload-logo', [GeneralSettingsController::class, 'uploadLogo'])->name('general.upload.logo');
        Route::post('/general/upload-favicon', [GeneralSettingsController::class, 'uploadFavicon'])->name('general.upload.favicon');
        Route::post('/general/reset-defaults', [GeneralSettingsController::class, 'resetToDefaults'])->name('general.reset');
        
        // Roles & Permissions
        Route::get('/roles', [RolesController::class, 'index'])->name('roles');
        Route::post('/roles/store', [RolesController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/update/{id}', [RolesController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/destroy/{id}', [RolesController::class, 'destroyRole'])->name('roles.destroy');
        Route::post('/roles/user/assign', [RolesController::class, 'assignUserRole'])->name('roles.user.assign');
        Route::put('/roles/user/update/{id}', [RolesController::class, 'updateUserRoles'])->name('roles.user.update');
        Route::post('/roles/permission/store', [RolesController::class, 'storePermission'])->name('roles.permission.store');
        Route::delete('/roles/permission/destroy/{id}', [RolesController::class, 'destroyPermission'])->name('roles.permission.destroy');
        Route::get('/roles/permissions', [RolesController::class, 'permissions'])->name('roles.permissions');
        
        // Academic Settings
        Route::get('/academic', [GeneralSettingsController::class, 'academic'])->name('academic');
        Route::put('/academic/update', [GeneralSettingsController::class, 'updateAcademic'])->name('academic.update');
        
        // Financial Settings
        Route::get('/financial', [GeneralSettingsController::class, 'financial'])->name('financial');
        Route::put('/financial/update', [GeneralSettingsController::class, 'updateFinancial'])->name('financial.update');
        
        // Email Settings
        Route::get('/email', [GeneralSettingsController::class, 'email'])->name('email');
        Route::put('/email/update', [GeneralSettingsController::class, 'updateEmail'])->name('email.update');
        Route::post('/email/test', [GeneralSettingsController::class, 'testEmail'])->name('email.test');
        
        // SMS Settings
        Route::get('/sms', [GeneralSettingsController::class, 'sms'])->name('sms');
        Route::put('/sms/update', [GeneralSettingsController::class, 'updateSMS'])->name('sms.update');
        
        // Backup & Restore
        Route::get('/backup', [GeneralSettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [GeneralSettingsController::class, 'createBackup'])->name('backup.create');
        Route::post('/backup/restore', [GeneralSettingsController::class, 'restoreBackup'])->name('backup.restore');
        Route::delete('/backup/delete/{filename}', [GeneralSettingsController::class, 'deleteBackup'])->name('backup.delete');
    });

    // =====================
    // NOTIFICATIONS
    // =====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::post('/send', [NotificationController::class, 'send'])->name('send');
        Route::get('/templates', [NotificationController::class, 'templates'])->name('templates');
        Route::post('/templates/store', [NotificationController::class, 'storeTemplate'])->name('templates.store');
    });

    // =====================
    // PROFILE MANAGEMENT
    // =====================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/update-password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/update-avatar', [ProfileController::class, 'updateAvatar'])->name('update-avatar');
        Route::get('/activity-log', [ProfileController::class, 'activityLog'])->name('activity-log');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::post('/two-factor', [ProfileController::class, 'toggleTwoFactor'])->name('two-factor');
    });

    // =====================
    // PROGRAMMES MANAGEMENT
    // =====================
    Route::prefix('programmes')->name('programmes.')->group(function () {
        Route::get('/', [ProgrammeController::class, 'index'])->name('index');
        Route::get('/create', [ProgrammeController::class, 'create'])->name('create');
        Route::post('/', [ProgrammeController::class, 'store'])->name('store');
        Route::get('/{programme}/edit', [ProgrammeController::class, 'edit'])->name('edit');
        Route::put('/{programme}', [ProgrammeController::class, 'update'])->name('update');
        Route::delete('/{programme}', [ProgrammeController::class, 'destroy'])->name('destroy');
        Route::post('/{programme}/toggle-status', [ProgrammeController::class, 'toggleStatus'])->name('toggle-status');
        
        // Programme Fees Routes (Normal Fees)
        Route::prefix('{programme}/fees')->name('fees.')->group(function () {
            Route::get('/', [ProgrammeFeeController::class, 'index'])->name('index');
            Route::get('/create', [ProgrammeFeeController::class, 'create'])->name('create');
            Route::post('/', [ProgrammeFeeController::class, 'store'])->name('store');
            Route::get('/{fee}/edit', [ProgrammeFeeController::class, 'edit'])->name('edit');
            Route::put('/{fee}', [ProgrammeFeeController::class, 'update'])->name('update');
            Route::delete('/{fee}', [ProgrammeFeeController::class, 'destroy'])->name('destroy');
            Route::post('/copy-fees', [ProgrammeFeeController::class, 'copyFees'])->name('copy-fees');
        });
        
        // SUPPLEMENTARY FEES (EXAMS ONLY)
        Route::prefix('{programme}/supplementary-fees')->name('supplementary-fees.')->group(function () {
            Route::get('/', [SupplementaryFeeController::class, 'index'])->name('index');
            Route::get('/create', [SupplementaryFeeController::class, 'create'])->name('create');
            Route::post('/', [SupplementaryFeeController::class, 'store'])->name('store');
            Route::get('/{fee}/edit', [SupplementaryFeeController::class, 'edit'])->name('edit');
            Route::put('/{fee}', [SupplementaryFeeController::class, 'update'])->name('update');
            Route::delete('/{fee}', [SupplementaryFeeController::class, 'destroy'])->name('destroy');
            Route::post('/copy-fees', [SupplementaryFeeController::class, 'copyFees'])->name('copy-fees');
            Route::get('/bulk-create', [SupplementaryFeeController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [SupplementaryFeeController::class, 'bulkStore'])->name('bulk-store');
        });
        
        // REPEAT MODULE FEES (FULL COURSE)
        Route::prefix('{programme}/repeat-module-fees')->name('repeat-module-fees.')->group(function () {
            Route::get('/', [RepeatModuleFeeController::class, 'index'])->name('index');
            Route::get('/create', [RepeatModuleFeeController::class, 'create'])->name('create');
            Route::post('/', [RepeatModuleFeeController::class, 'store'])->name('store');
            Route::get('/{fee}/edit', [RepeatModuleFeeController::class, 'edit'])->name('edit');
            Route::put('/{fee}', [RepeatModuleFeeController::class, 'update'])->name('update');
            Route::delete('/{fee}', [RepeatModuleFeeController::class, 'destroy'])->name('destroy');
            Route::post('/copy-fees', [RepeatModuleFeeController::class, 'copyFees'])->name('copy-fees');
            Route::get('/bulk-create', [RepeatModuleFeeController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [RepeatModuleFeeController::class, 'bulkStore'])->name('bulk-store');
        });
        
        // HOSTEL FEES (ACCOMMODATION)
        Route::prefix('{programme}/hostel-fees')->name('hostel-fees.')->group(function () {
            Route::get('/', [HostelFeeController::class, 'index'])->name('index');
            Route::get('/create', [HostelFeeController::class, 'create'])->name('create');
            Route::post('/', [HostelFeeController::class, 'store'])->name('store');
            Route::get('/{fee}/edit', [HostelFeeController::class, 'edit'])->name('edit');
            Route::put('/{fee}', [HostelFeeController::class, 'update'])->name('update');
            Route::delete('/{fee}', [HostelFeeController::class, 'destroy'])->name('destroy');
            Route::post('/copy-fees', [HostelFeeController::class, 'copyFees'])->name('copy-fees');
            Route::get('/bulk-create', [HostelFeeController::class, 'bulkCreate'])->name('bulk-create');
            Route::post('/bulk-store', [HostelFeeController::class, 'bulkStore'])->name('bulk-store');
        });
    });
  // Module management
Route::resource('modules', ModuleController::class);

// Curriculum (module registration)
Route::prefix('curriculum')->name('curriculum.')->group(function () {
    Route::get('/', [CurriculumController::class, 'index'])->name('index');
    Route::post('/', [CurriculumController::class, 'store'])->name('store');
    Route::delete('{id}', [CurriculumController::class, 'destroy'])->name('destroy');
});




    // =====================
    // GRADING SYSTEMS MANAGEMENT
    // =====================
    Route::prefix('grading-systems')->name('grading-systems.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'store'])->name('store');
        Route::get('/{gradingSystem}/edit', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'edit'])->name('edit');
        Route::put('/{gradingSystem}', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'update'])->name('update');
        Route::delete('/{gradingSystem}', [\App\Http\Controllers\SuperAdmin\GradingSystemController::class, 'destroy'])->name('destroy');
       Route::post('/{gradingSystem}/toggle', [GradingSystemController::class, 'toggleActive'])->name('toggle-active');
    });

 // ============ PAYMENT ADJUSTMENT REQUESTS (Super Admin) ============
Route::prefix('payment-adjustments')->name('payment-adjustments.')->group(function () {
    
    // ⚠️ KUMBUKA: Static routes lazima ziwe kabla ya dynamic routes ({id})
    
    // Tafuta student (static route)
    Route::get('/search-student', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'searchStudent'])->name('search-student');
    
    // Fomu ya direct adjustment (static route)
    Route::get('/students/{student}/create-direct', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'createDirect'])->name('create-direct');
    
    // Process direct adjustment
    Route::post('/students/{student}/process-direct', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'processDirect'])->name('process-direct');
    
    // Orodha ya maombi yote (index)
    Route::get('/', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'allRequests'])->name('index');
    
    
    Route::get('/{id}', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'show'])->name('show');
    
    // Futa ombi (dynamic)
    Route::delete('/{id}', [App\Http\Controllers\Finance\PaymentAdjustmentRequestController::class, 'destroy'])->name('destroy');
});

    // =====================
    // ASSESSMENT COMPONENTS MANAGEMENT
    // =====================
    Route::prefix('assessment-components')->name('assessment-components.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'store'])->name('store');
        Route::get('/{component}/edit', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'edit'])->name('edit');
        Route::put('/{component}', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'update'])->name('update');
        Route::delete('/{component}', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'destroy'])->name('destroy');
        Route::post('/{component}/toggle', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'toggleActive'])->name('toggle');
        Route::post('/copy-from-module', [\App\Http\Controllers\SuperAdmin\AssessmentComponentController::class, 'copyFromModule'])->name('copy-from-module');
    });

 // Add this route inside the results group
Route::prefix('results')->name('results.')->group(function () {
    
    // View Routes
    Route::get('/', [ResultController::class, 'index'])->name('index');
    Route::get('/create', [ResultController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [ResultController::class, 'edit'])->name('edit');
    
    // ADD THIS MISSING ROUTE
    Route::get('/bulk-upload', [ResultController::class, 'showBulkUpload'])->name('bulk-upload.form');
    Route::post('/bulk-upload', [ResultController::class, 'processBulkUpload'])->name('bulk-upload.process');
    
    // API Routes (AJAX)
    Route::prefix('api')->name('api.')->group(function () {
        // ... existing API routes ...
        Route::get('/list', [ResultController::class, 'getList'])->name('list');
        Route::get('/students/search', [ResultController::class, 'searchStudents'])->name('students.search');
        Route::get('/student/by-reg-number', [ResultController::class, 'getStudentByRegNumber'])->name('student.by-reg-number');
        Route::get('/modules/search', [ResultController::class, 'searchModules'])->name('modules.search');
        Route::get('/modules/{moduleId}/weights', [ResultController::class, 'getModuleWeights'])->name('modules.weights');
        Route::get('/calculate-score', [ResultController::class, 'calculateScore'])->name('calculate-score');
        Route::get('/dashboard-stats', [ResultController::class, 'getDashboardStats'])->name('dashboard-stats');
        Route::get('/programme/{programmeId}/levels', [ResultController::class, 'getProgrammeLevels'])->name('programme.levels');
        Route::get('/transcript/{studentId}', [ResultController::class, 'getStudentTranscript'])->name('transcript');
        Route::get('/gpa/{studentId}', [ResultController::class, 'getStudentGPA'])->name('gpa');
        
        // CRUD Operations
        Route::post('/store', [ResultController::class, 'store'])->name('store');
        Route::put('/{id}', [ResultController::class, 'update'])->name('update');
        Route::delete('/{id}', [ResultController::class, 'destroy'])->name('destroy');
        // In routes/superadmin.php

        // Bulk Operations
        Route::post('/bulk-approve', [ResultController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-publish', [ResultController::class, 'bulkPublish'])->name('bulk-publish');
        Route::delete('/bulk-delete', [ResultController::class, 'bulkDelete'])->name('bulk-delete');
    });
});
    // =====================
    // RESULT BATCH MANAGEMENT
    // =====================
    Route::prefix('result-batches')->name('result-batches.')->group(function () {
        
        Route::get('/', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'show'])->name('show');
        Route::delete('/{id}', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'reject'])->name('reject');
        
        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/list', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'getList'])->name('list');
            Route::get('/{id}/results', [\App\Http\Controllers\SuperAdmin\ResultBatchController::class, 'getBatchResults'])->name('results');
        });
    });

    // =====================
    // RESULT PUBLISHING (Super Admin Only)
    // =====================
    Route::prefix('publishing')->name('publishing.')->group(function () {
        
        Route::get('/', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'index'])->name('index');
        Route::post('/publish-all', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'publishAll'])->name('publish-all');
        Route::post('/publish-by-department', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'publishByDepartment'])->name('publish-by-department');
        Route::post('/publish-by-programme', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'publishByProgramme'])->name('publish-by-programme');
        Route::post('/unpublish/{id}', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'unpublish'])->name('unpublish');
        
        // Schedule Publishing
        Route::post('/schedule', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'schedulePublishing'])->name('schedule');
        Route::get('/scheduled', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'scheduledPublishing'])->name('scheduled');
        Route::delete('/scheduled/{id}', [\App\Http\Controllers\SuperAdmin\ResultPublishingController::class, 'cancelScheduled'])->name('cancel-scheduled');
    });

    // =====================
    // RESULT INTEGRITY ENGINE
    // =====================
    Route::prefix('integrity')->name('integrity.')->group(function () {
        
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'dashboard'])->name('dashboard');
        Route::get('/run-checks', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'runChecks'])->name('run-checks');
        Route::get('/anomalies', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'anomalies'])->name('anomalies');
        Route::post('/repair/{anomalyId}', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'repair'])->name('repair');
        Route::post('/bulk-repair', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'bulkRepair'])->name('bulk-repair');
        Route::get('/logs', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'logs'])->name('logs');
        
        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/stats', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'getStats'])->name('stats');
            Route::get('/check-grading', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'checkGrading'])->name('check-grading');
            Route::get('/check-weights', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'checkWeights'])->name('check-weights');
            Route::get('/check-duplicates', [\App\Http\Controllers\SuperAdmin\ResultIntegrityController::class, 'checkDuplicates'])->name('check-duplicates');
        });
    });

    // =====================
    // RESULT TEMPLATES MANAGEMENT
    // =====================
    Route::prefix('result-templates')->name('result-templates.')->group(function () {
        
        Route::get('/', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-default', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'setDefault'])->name('set-default');
        
        // API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/list', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'getList'])->name('list');
            Route::post('/preview', [\App\Http\Controllers\SuperAdmin\ResultTemplateController::class, 'preview'])->name('preview');
        });
    });
    // =====================
    // RESULT APPROVAL (Super Admin Override)
    // =====================
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/results', [\App\Http\Controllers\SuperAdmin\ResultApprovalController::class, 'index'])->name('results');
        Route::post('/results/{result}/approve', [\App\Http\Controllers\SuperAdmin\ResultApprovalController::class, 'approve'])->name('results.approve');
        Route::post('/results/{result}/reject', [\App\Http\Controllers\SuperAdmin\ResultApprovalController::class, 'reject'])->name('results.reject');
        Route::post('/results/{result}/force-publish', [\App\Http\Controllers\SuperAdmin\ResultApprovalController::class, 'forcePublish'])->name('results.force-publish');
    });

    Route::prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/results', [ResultApprovalController::class, 'index'])->name('results');
    Route::get('/history', [ResultApprovalController::class, 'history'])->name('history');
});

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/academic-performance', [ReportController::class, 'academicPerformance'])->name('academic-performance');
});

// TRANSCRIPTS
// =====================
Route::prefix('transcripts')->name('transcripts.')->group(function () {
    Route::get('/', [TranscriptController::class, 'index'])->name('index');
    Route::get('/generate/{studentId}', [TranscriptController::class, 'generate'])->name('generate');
    Route::get('/download/{studentId}', [TranscriptController::class, 'download'])->name('download');
});


    // SEMESTER RESULTS
// =====================
Route::get('/results/by-semester', [ResultController::class, 'bySemester'])->name('results.by-semester');
Route::get('/results/by-programme', [ResultController::class, 'byProgramme'])->name('results.by-programme');
Route::get('/results/student-search', [ResultController::class, 'studentSearch'])->name('results.student-search');

    // =====================
    // RESULT INTEGRITY ENGINE (Admin Tools)
    // =====================
    Route::prefix('integrity')->name('integrity.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\IntegrityController::class, 'dashboard'])->name('dashboard');
        Route::get('/checks', [\App\Http\Controllers\SuperAdmin\IntegrityController::class, 'runChecks'])->name('run-checks');
        Route::post('/repair', [\App\Http\Controllers\SuperAdmin\IntegrityController::class, 'repair'])->name('repair');
        Route::get('/logs', [\App\Http\Controllers\SuperAdmin\IntegrityController::class, 'logs'])->name('logs');
    });
    // =====================
    // FEE MANAGEMENT (GLOBAL)
    // =====================
    Route::prefix('fee-management')->name('fee-management.')->group(function () {
        // Programme Fees (Normal)
        Route::get('/settings', [ProgrammeFeeController::class, 'globalSettings'])->name('settings');
        Route::get('/transactions', [ProgrammeFeeController::class, 'transactions'])->name('transactions');
        Route::get('/reports', [ProgrammeFeeController::class, 'reports'])->name('reports');
        
        // SUPPLEMENTARY FEE MANAGEMENT
        Route::prefix('supplementary')->name('supplementary.')->group(function () {
            Route::get('/settings', [SupplementaryFeeController::class, 'globalSettings'])->name('settings');
            Route::get('/transactions', [SupplementaryFeeController::class, 'transactions'])->name('transactions');
            Route::get('/reports', [SupplementaryFeeController::class, 'reports'])->name('reports');
            Route::get('/summary', [SupplementaryFeeController::class, 'summary'])->name('summary');
            Route::get('/by-programme', [SupplementaryFeeController::class, 'byProgramme'])->name('by-programme');
            Route::get('/by-academic-year', [SupplementaryFeeController::class, 'byAcademicYear'])->name('by-academic-year');
            Route::get('/configuration', [SupplementaryFeeController::class, 'configuration'])->name('configuration');
            Route::post('/configuration/update', [SupplementaryFeeController::class, 'updateConfiguration'])->name('configuration.update');
        });
        
        // REPEAT MODULE FEE MANAGEMENT
        Route::prefix('repeat-module')->name('repeat-module.')->group(function () {
            Route::get('/settings', [RepeatModuleFeeController::class, 'globalSettings'])->name('settings');
            Route::get('/transactions', [RepeatModuleFeeController::class, 'transactions'])->name('transactions');
            Route::get('/reports', [RepeatModuleFeeController::class, 'reports'])->name('reports');
            Route::get('/summary', [RepeatModuleFeeController::class, 'summary'])->name('summary');
            Route::get('/by-programme', [RepeatModuleFeeController::class, 'byProgramme'])->name('by-programme');
            Route::get('/by-academic-year', [RepeatModuleFeeController::class, 'byAcademicYear'])->name('by-academic-year');
            Route::get('/configuration', [RepeatModuleFeeController::class, 'configuration'])->name('configuration');
            Route::post('/configuration/update', [RepeatModuleFeeController::class, 'updateConfiguration'])->name('configuration.update');
        });
    });

    // =====================
    // AJAX & API ENDPOINTS
    // =====================
    Route::prefix('ajax')->name('ajax.')->group(function () {
        // Dashboard Stats
        Route::get('/stats', [SuperAdminController::class, 'getStats'])->name('stats');
        Route::get('/chart-data', [SuperAdminController::class, 'getChartData'])->name('chart-data');
        Route::get('/recent-activities', [SuperAdminController::class, 'getRecentActivities'])->name('recent-activities');
        
        // Quick Actions
        Route::post('/quick-user', [UserController::class, 'quickCreate'])->name('quick-user.create');
        Route::post('/quick-student', [StudentController::class, 'quickCreate'])->name('quick-student.create');
        
        // Search
        Route::get('/search/users', [UserController::class, 'search'])->name('search.users');
        Route::get('/search/students', [StudentController::class, 'search'])->name('search.students');
        Route::get('/search/staff', [StaffController::class, 'search'])->name('search.staff');
        
        // Status Updates
        Route::post('/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('toggle-status');
        
        // File Uploads
        Route::post('/upload-file', [SuperAdminController::class, 'uploadFile'])->name('upload-file');
        Route::post('/upload-image', [SuperAdminController::class, 'uploadImage'])->name('upload-image');
        
        // System Info
        Route::get('/system-info', [SuperAdminController::class, 'systemInfo'])->name('system-info');
        Route::get('/server-status', [SuperAdminController::class, 'serverStatus'])->name('server-status');
        
        // SUPPLEMENTARY STATS
        Route::prefix('supplementary')->name('supplementary.')->group(function () {
            Route::get('/stats', [SuperAdminController::class, 'supplementaryStats'])->name('stats');
            Route::get('/chart-data', [SuperAdminController::class, 'supplementaryChartData'])->name('chart-data');
            Route::get('/recent-payments', [SuperAdminController::class, 'supplementaryRecentPayments'])->name('recent-payments');
            Route::get('/get-fee/{programme}/{level}/{semester}', [SupplementaryFeeController::class, 'getFee'])->name('get-fee');
            Route::get('/check-exists', [SupplementaryFeeController::class, 'checkExists'])->name('check-exists');
        });
        
        // REPEAT MODULE STATS
        Route::prefix('repeat-module')->name('repeat-module.')->group(function () {
            Route::get('/stats', [SuperAdminController::class, 'repeatModuleStats'])->name('stats');
            Route::get('/chart-data', [SuperAdminController::class, 'repeatModuleChartData'])->name('chart-data');
            Route::get('/recent-payments', [SuperAdminController::class, 'repeatModuleRecentPayments'])->name('recent-payments');
            Route::get('/get-fee/{programme}/{level}/{semester}', [RepeatModuleFeeController::class, 'getFee'])->name('get-fee');
            Route::get('/check-exists', [RepeatModuleFeeController::class, 'checkExists'])->name('check-exists');
        });
    });

    // =====================
    // UTILITIES
    // =====================
    Route::prefix('utilities')->name('utilities.')->group(function () {
        // Database
        Route::get('/database', [SuperAdminController::class, 'database'])->name('database');
        Route::post('/database/optimize', [SuperAdminController::class, 'optimizeDatabase'])->name('database.optimize');
        Route::post('/database/cleanup', [SuperAdminController::class, 'cleanupDatabase'])->name('database.cleanup');
        
        // Cache
        Route::get('/cache', [SuperAdminController::class, 'cache'])->name('cache');
        Route::post('/cache/clear', [SuperAdminController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/clear-view', [SuperAdminController::class, 'clearViewCache'])->name('cache.clear-view');
        Route::post('/cache/clear-config', [SuperAdminController::class, 'clearConfigCache'])->name('cache.clear-config');
        Route::post('/cache/clear-route', [SuperAdminController::class, 'clearRouteCache'])->name('cache.clear-route');
        
        // Logs
        Route::get('/logs', [SuperAdminController::class, 'logs'])->name('logs');
        Route::get('/logs/{filename}', [SuperAdminController::class, 'viewLog'])->name('logs.view');
        Route::delete('/logs/{filename}', [SuperAdminController::class, 'deleteLog'])->name('logs.delete');
        Route::post('/logs/clear', [SuperAdminController::class, 'clearLogs'])->name('logs.clear');
        
        // System Tools
        Route::get('/tools', [SuperAdminController::class, 'tools'])->name('tools');
        Route::post('/tools/maintenance', [SuperAdminController::class, 'toggleMaintenance'])->name('tools.maintenance');
        Route::post('/tools/email-test', [SuperAdminController::class, 'testEmailConnection'])->name('tools.email-test');
        Route::post('/tools/sms-test', [SuperAdminController::class, 'testSMSConnection'])->name('tools.sms-test');
    });
    // In routes/superadmin.php
Route::get('/results/transcript/{studentId}', [ResultController::class, 'transcriptView'])->name('results.transcript');

    // =====================
    // BULK OPERATIONS
    // =====================
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('/users/import', [UserController::class, 'bulkImport'])->name('users.import');
        Route::post('/students/import', [StudentController::class, 'bulkImport'])->name('students.import');
        Route::post('/staff/import', [StaffController::class, 'bulkImport'])->name('staff.import');
        Route::post('/courses/import', [CourseController::class, 'bulkImport'])->name('courses.import');
        Route::post('/users/status', [UserController::class, 'bulkStatus'])->name('users.status');
        Route::post('/students/enroll', [StudentController::class, 'bulkEnroll'])->name('students.enroll');
        Route::post('/applications/process', [ApplicationController::class, 'bulkProcess'])->name('applications.process');
        Route::post('/payments/process', [PaymentController::class, 'bulkProcess'])->name('payments.process');
        
        // Bulk Supplementary Fees
        Route::post('/supplementary-fees/create', [SupplementaryFeeController::class, 'bulkCreate'])->name('supplementary-fees.create');
        Route::post('/supplementary-fees/update', [SupplementaryFeeController::class, 'bulkUpdate'])->name('supplementary-fees.update');
        
        // Bulk Repeat Module Fees
        Route::post('/repeat-module-fees/create', [RepeatModuleFeeController::class, 'bulkCreate'])->name('repeat-module-fees.create');
        Route::post('/repeat-module-fees/update', [RepeatModuleFeeController::class, 'bulkUpdate'])->name('repeat-module-fees.update');
    });

    // Invoice Management Routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
        
        // Payment routes
        Route::post('/{id}/payments', [InvoiceController::class, 'addPayment'])->name('payments.add');
        
        // PDF and Email
        Route::get('/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('pdf');
        Route::post('/{id}/email', [InvoiceController::class, 'sendEmail'])->name('email');
        
        // API-like routes for AJAX
        Route::post('/generate-for-student', [InvoiceController::class, 'generateForStudent'])->name('generate-student');
        Route::get('/student/{studentId}', [InvoiceController::class, 'getStudentInvoices'])->name('student-invoices');
        Route::get('/statistics', [InvoiceController::class, 'statistics'])->name('statistics');
    });
});