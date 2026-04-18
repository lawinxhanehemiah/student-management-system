<?php

use App\Http\Controllers\DpAcademics\AuditController;
use App\Http\Controllers\DpAcademics\AvnController;
use App\Http\Controllers\DpAcademics\StudentController;
use App\Http\Controllers\DpAcademics\CurriculumController;
use App\Http\Controllers\DpAcademics\AssessmentController;
use App\Http\Controllers\DpAcademics\ReassessmentController;
use App\Http\Controllers\DpAcademics\StaffController;
use App\Http\Controllers\DpAcademics\IptController;
use App\Http\Controllers\DpAcademics\ExportController;
use App\Http\Controllers\DpAcademics\DashboardController;

Route::middleware(['auth', 'role:Deputy_Principal_Academics'])
    ->prefix('dp/academics')
    ->name('dp.academics.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Audit Trail
        Route::get('/audit/logs', [AuditController::class, 'logs'])->name('audit.logs');

        // AVN Validation
        Route::get('/avn/verification', [AvnController::class, 'verification'])->name('avn.verification');

        // Financial Gatekeeping & Student Registry
        Route::get('/students/financial-clearance', [StudentController::class, 'financialClearance'])->name('students.financial-clearance');
        Route::get('/students/active-registry', [StudentController::class, 'activeRegistry'])->name('students.active-registry');

        // Curriculum Architecture
        Route::get('/curriculum/nta-levels', [CurriculumController::class, 'ntaLevels'])->name('curriculum.nta-levels');
        Route::get('/curriculum/competence-elements', [CurriculumController::class, 'competenceElements'])->name('curriculum.competence-elements');
        Route::get('/curriculum/versioning', [CurriculumController::class, 'versioning'])->name('curriculum.versioning');

        // Assessment Management
        Route::get('/assessment/marks-entry', [AssessmentController::class, 'marksEntry'])->name('assessment.marks-entry');
        Route::get('/assessment/moderation', [AssessmentController::class, 'moderation'])->name('assessment.moderation');
        Route::get('/assessment/grading-engine', [AssessmentController::class, 'gradingEngine'])->name('assessment.grading-engine');
        Route::get('/assessment/locking', [AssessmentController::class, 'locking'])->name('assessment.locking');

        // Re-assessment Module
        Route::get('/reassessment/eligible', [ReassessmentController::class, 'eligible'])->name('reassessment.eligible');
        Route::get('/reassessment/scheduling', [ReassessmentController::class, 'scheduling'])->name('reassessment.scheduling');
        Route::get('/reassessment/supplementary', [ReassessmentController::class, 'supplementary'])->name('reassessment.supplementary');

        // Staff Roles & Mapping
        Route::get('/staff/assign-instructors', [StaffController::class, 'assignInstructors'])->name('staff.assign-instructors');
        Route::get('/staff/assign-verifiers', [StaffController::class, 'assignVerifiers'])->name('staff.assign-verifiers');
        Route::get('/staff/workload', [StaffController::class, 'workload'])->name('staff.workload');

        // Industrial Training (IPT)
        Route::get('/ipt/placements', [IptController::class, 'placements'])->name('ipt.placements');
        Route::get('/ipt/assessment', [IptController::class, 'assessment'])->name('ipt.assessment');

        // NACTVET Export
        Route::get('/exports/nactvet', [ExportController::class, 'nactvet'])->name('exports.nactvet');
    });