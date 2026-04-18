<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lecturer\ResultController;
use App\Http\Controllers\Tutor\{
    DashboardController,
    StudentController,
    CourseController,
    SessionController,
    AssessmentController,
    MessageController,
    ReportController,
    ProfileController,
    MaterialController,
    AssignmentController,
    AttendanceController,
    GradeController,
    ExamController,
    HelpController
};

/*
|--------------------------------------------------------------------------
| Tutor Routes
|--------------------------------------------------------------------------
|
| All routes for tutor module - accessed via prefix 'tutor'
|
*/

Route::prefix('tutor')->name('tutor.')->group(function () {
    
    // Apply auth middleware only (remove tutor middleware)
    Route::middleware(['auth'])->group(function () {
        
        // ==================== CORE MENU ====================
        
        // 1. Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [DashboardController::class, 'index'])->name('home');
        
        // 2. My Students
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::get('/active', [StudentController::class, 'active'])->name('active');
            Route::get('/pending', [StudentController::class, 'pending'])->name('pending');
            Route::get('/graduated', [StudentController::class, 'graduated'])->name('graduated');
            Route::get('/{student}', [StudentController::class, 'show'])->name('show');
            Route::get('/{student}/progress', [StudentController::class, 'progress'])->name('progress');
            Route::post('/{student}/approve', [StudentController::class, 'approve'])->name('approve');
            Route::post('/{student}/reject', [StudentController::class, 'reject'])->name('reject');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        });
        
        // 3. Courses
        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::get('/create', [CourseController::class, 'create'])->name('create');
            Route::post('/', [CourseController::class, 'store'])->name('store');
            Route::get('/{course}', [CourseController::class, 'show'])->name('show');
            Route::get('/{course}/edit', [CourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [CourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [CourseController::class, 'destroy'])->name('destroy');
            Route::post('/{course}/publish', [CourseController::class, 'publish'])->name('publish');
            Route::post('/{course}/archive', [CourseController::class, 'archive'])->name('archive');
        });
        
        // 3a. Learning Materials
        Route::prefix('materials')->name('materials.')->group(function () {
            Route::get('/', [MaterialController::class, 'index'])->name('index');
            Route::get('/create', [MaterialController::class, 'create'])->name('create');
            Route::post('/', [MaterialController::class, 'store'])->name('store');
            Route::get('/{material}', [MaterialController::class, 'show'])->name('show');
            Route::get('/{material}/edit', [MaterialController::class, 'edit'])->name('edit');
            Route::put('/{material}', [MaterialController::class, 'update'])->name('update');
            Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('destroy');
            Route::post('/{material}/download', [MaterialController::class, 'download'])->name('download');
        });
        
        // 3b. Assignments
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [AssignmentController::class, 'index'])->name('index');
            Route::get('/create', [AssignmentController::class, 'create'])->name('create');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
            Route::get('/{assignment}/edit', [AssignmentController::class, 'edit'])->name('edit');
            Route::put('/{assignment}', [AssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [AssignmentController::class, 'destroy'])->name('destroy');
            Route::get('/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('submissions');
            Route::post('/{assignment}/submission/{submission}/grade', [AssignmentController::class, 'grade'])->name('grade.submission');
        });
        
        // 4. Sessions
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', [SessionController::class, 'index'])->name('index');
            Route::get('/upcoming', [SessionController::class, 'upcoming'])->name('upcoming');
            Route::get('/past', [SessionController::class, 'past'])->name('past');
            Route::get('/schedule', [SessionController::class, 'schedule'])->name('schedule');
            Route::get('/create', [SessionController::class, 'create'])->name('create');
            Route::post('/', [SessionController::class, 'store'])->name('store');
            Route::get('/{session}', [SessionController::class, 'show'])->name('show');
            Route::get('/{session}/edit', [SessionController::class, 'edit'])->name('edit');
            Route::put('/{session}', [SessionController::class, 'update'])->name('update');
            Route::delete('/{session}', [SessionController::class, 'destroy'])->name('destroy');
            Route::post('/{session}/cancel', [SessionController::class, 'cancel'])->name('cancel');
            Route::post('/{session}/reschedule', [SessionController::class, 'reschedule'])->name('reschedule');
        });
        
        // 4a. Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/session/{session}', [AttendanceController::class, 'session'])->name('session');
            Route::post('/session/{session}/mark', [AttendanceController::class, 'mark'])->name('mark');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
            Route::get('/student/{student}', [AttendanceController::class, 'student'])->name('student');
            Route::post('/bulk', [AttendanceController::class, 'bulkMark'])->name('bulk');
        });
        
        // 5. Assessments
        Route::prefix('assessments')->name('assessments.')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])->name('index');
        });
        
        // 5a. Grades
        Route::prefix('grades')->name('grades.')->group(function () {
            Route::get('/', [GradeController::class, 'index'])->name('index');
            Route::get('/course/{course}', [GradeController::class, 'course'])->name('course');
            Route::get('/student/{student}', [GradeController::class, 'student'])->name('student');
            Route::post('/update', [GradeController::class, 'update'])->name('update');
            Route::post('/bulk-update', [GradeController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/export/{course}', [GradeController::class, 'export'])->name('export');
        });
        
        // 5b. Exams & Quizzes
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [ExamController::class, 'index'])->name('index');
            Route::get('/create', [ExamController::class, 'create'])->name('create');
            Route::post('/', [ExamController::class, 'store'])->name('store');
            Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
            Route::get('/{exam}/edit', [ExamController::class, 'edit'])->name('edit');
            Route::put('/{exam}', [ExamController::class, 'update'])->name('update');
            Route::delete('/{exam}', [ExamController::class, 'destroy'])->name('destroy');
            Route::post('/{exam}/publish', [ExamController::class, 'publish'])->name('publish');
            Route::get('/{exam}/results', [ExamController::class, 'results'])->name('results');
            Route::post('/{exam}/results/{result}/grade', [ExamController::class, 'gradeExam'])->name('grade');
        });
        
        // 5c. Results Management (single block, using tutor.results.* names)
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/', [ResultController::class, 'index'])->name('index');
            Route::get('/consolidated', [ResultController::class, 'consolidated'])->name('consolidated');
            Route::get('/approval-status', [ResultController::class, 'approvalStatus'])->name('approval-status');
            Route::get('/ministry-import', [ResultController::class, 'ministryImportForm'])->name('ministry-import.form');
            Route::post('/ministry-import', [ResultController::class, 'ministryImport'])->name('ministry-import');
            Route::get('/modules/{module}/create/{academicYearId}/{semester}', [ResultController::class, 'create'])->name('create');
            Route::post('/modules/{module}/store/{academicYearId}/{semester}', [ResultController::class, 'store'])->name('store');
            Route::post('/{result}/submit', [ResultController::class, 'submitToHod'])->name('submit');
            Route::post('/{result}/lock', [ResultController::class, 'lock'])->name('lock');
            Route::post('/{result}/unlock', [ResultController::class, 'unlock'])->name('unlock');
        });
        
        // 5d. Student Submissions & Feedback
        Route::get('/submissions', [AssessmentController::class, 'submissions'])->name('submissions');
        Route::get('/feedback', [AssessmentController::class, 'feedback'])->name('feedback');
        Route::post('/feedback/{feedback}/reply', [AssessmentController::class, 'replyFeedback'])->name('feedback.reply');
        
        // ==================== OPTIONAL MENU ====================
        
        // 6. Messages
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::get('/inbox', [MessageController::class, 'inbox'])->name('inbox');
            Route::get('/sent', [MessageController::class, 'sent'])->name('sent');
            Route::get('/create', [MessageController::class, 'create'])->name('create');
            Route::post('/', [MessageController::class, 'store'])->name('store');
            Route::get('/{message}', [MessageController::class, 'show'])->name('show');
            Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
            Route::post('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
            Route::post('/mark-read/{message}', [MessageController::class, 'markRead'])->name('mark-read');
        });
        
        // 6a. Announcements
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [MessageController::class, 'announcements'])->name('index');
            Route::get('/create', [MessageController::class, 'createAnnouncement'])->name('create');
            Route::post('/', [MessageController::class, 'storeAnnouncement'])->name('store');
            Route::get('/{announcement}', [MessageController::class, 'showAnnouncement'])->name('show');
            Route::delete('/{announcement}', [MessageController::class, 'destroyAnnouncement'])->name('destroy');
        });
        
        // 7. Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/performance', [ReportController::class, 'performance'])->name('performance');
            Route::get('/course-analytics', [ReportController::class, 'courseAnalytics'])->name('course-analytics');
            Route::get('/attendance-report', [ReportController::class, 'attendanceReport'])->name('attendance-report');
            Route::get('/export-performance', [ReportController::class, 'exportPerformance'])->name('export-performance');
            Route::get('/export-attendance', [ReportController::class, 'exportAttendance'])->name('export-attendance');
            Route::get('/analytics-data', [ReportController::class, 'analyticsData'])->name('analytics-data');
        });
        
        // 8. Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
            Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
            Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
            Route::put('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications.update');
            Route::get('/availability', [ProfileController::class, 'availability'])->name('availability');
            Route::put('/availability', [ProfileController::class, 'updateAvailability'])->name('availability.update');
            Route::get('/password', [ProfileController::class, 'password'])->name('password');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        });
        
        // 9. Help & Support
        Route::prefix('help')->name('help.')->group(function () {
            Route::get('/', [HelpController::class, 'index'])->name('index');
            Route::get('/faq', [HelpController::class, 'faq'])->name('faq');
            Route::get('/tutorials', [HelpController::class, 'tutorials'])->name('tutorials');
            Route::post('/ticket', [HelpController::class, 'submitTicket'])->name('ticket');
            Route::get('/guides/{guide}', [HelpController::class, 'guide'])->name('guide');
        });
        
        // Additional Routes
        Route::get('/calendar', [SessionController::class, 'calendar'])->name('calendar');
        Route::get('/notifications/mark-all', [ProfileController::class, 'markAllNotifications'])->name('notifications.mark-all');
        Route::get('/search', [DashboardController::class, 'search'])->name('search');
        
        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard-stats', [DashboardController::class, 'stats'])->name('dashboard-stats');
            Route::get('/students/{course}/list', [StudentController::class, 'listByCourse'])->name('students.by-course');
            Route::get('/attendance/session/{session}/data', [AttendanceController::class, 'sessionData'])->name('attendance.session-data');
            Route::get('/grades/course/{course}/data', [GradeController::class, 'courseData'])->name('grades.course-data');
        });
    });
});