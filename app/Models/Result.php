<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Result extends Model
{
    protected $fillable = [
        'student_id', 'module_id', 'academic_year_id', 'semester',
        'ca_score', 'exam_score', 'grade', 'grade_point', 'grading_system_id',
        'status', 'source', 'remarks', 'submitted_by', 'approved_by',
        'submitted_at', 'approved_at', 'version', 'is_current',
        'external_reference', 'import_batch_id', 'locked_by', 'locked_at',
        'change_reason', 'changed_by'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradingSystem()
    {
        return $this->belongsTo(GradingSystem::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function approvals()
    {
        return $this->hasMany(ResultApproval::class);
    }

    // Helpers
    public function calculateFinalScore()
    {
        if ($this->ca_score !== null && $this->exam_score !== null) {
            return round(($this->ca_score * 0.4) + ($this->exam_score * 0.6), 2);
        }
        // If using components, we'll compute from component_scores
        return null;
    }

    public function calculateGradeAndPoint()
    {
        $final = $this->calculateFinalScore();
        if ($final === null) return null;

        $grading = GradingSystem::getGrade($final, $this->academic_year_id);
        if ($grading) {
            $this->grade = $grading->grade;
            $this->grade_point = $grading->grade_point;
            $this->grading_system_id = $grading->id;
        } else {
            $this->grade = 'F';
            $this->grade_point = 0;
            $this->grading_system_id = null;
        }
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'draft' && $this->locked_by === null;
    }

    public function lock(User $user, $minutes = 30): void
    {
        $this->locked_by = $user->id;
        $this->locked_at = now();
        $this->save();
    }

    public function unlock(): void
    {
        $this->locked_by = null;
        $this->locked_at = null;
        $this->save();
    }

    public function isLocked(): bool
    {
        if (!$this->locked_at) return false;
        return now()->diffInMinutes($this->locked_at) < 30; // lock expires after 30 min
    }

    // Create a new version (for appeal or correction)
    public function createNewVersion(User $user, $reason): self
    {
        $new = $this->replicate();
        $new->version = $this->version + 1;
        $new->is_current = true;
        $new->status = 'draft';
        $new->change_reason = $reason;
        $new->changed_by = $user->id;
        $new->save();

        $this->is_current = false;
        $this->save();

        return $new;
    }
}