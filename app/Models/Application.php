<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_number',
        'user_id',
        'entry_level',
        'academic_year_id',
        'intake',
        'status',
        
        // Step completion flags
        'step_personal_completed',
        'step_contact_completed',
        'step_next_of_kin_completed',
        'step_academic_completed',
        'step_programs_completed',
        'step_documents_completed',
        'step_declaration_completed',
        
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'is_paid',
        'amount_paid',
        'payment_reference',
        'payment_date',
        'is_free_application',
        'fee_waiver_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'payment_date' => 'datetime',
        'is_paid' => 'boolean',
        'is_free_application' => 'boolean',
        'amount_paid' => 'decimal:2',
        
        // Step completion
        'step_personal_completed' => 'boolean',
        'step_contact_completed' => 'boolean',
        'step_next_of_kin_completed' => 'boolean',
        'step_academic_completed' => 'boolean',
        'step_programs_completed' => 'boolean',
        'step_documents_completed' => 'boolean',
        'step_declaration_completed' => 'boolean',
    ];

    /**
     * Generate application number
     */
    public static function generateApplicationNumber($academicYear, $intake)
    {
        $yearCode = substr($academicYear, 2, 2); // 2024 -> 24
        $intakeCode = $intake === 'March' ? '01' : '02';
        
        $lastApp = self::where('application_number', 'like', "APP{$yearCode}{$intakeCode}%")
            ->orderBy('application_number', 'desc')
            ->first();
        
        if ($lastApp) {
            $lastSeq = intval(substr($lastApp->application_number, -4));
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "APP{$yearCode}{$intakeCode}{$sequence}";
    }

    /**
     * Check if application is submitted
     */
    public function isSubmitted(): bool
    {
        return !in_array($this->status, ['draft', 'withdrawn']);
    }

    /**
     * Check if application is accepted
     */
    public function isAccepted(): bool
    {
        return in_array($this->status, ['accepted', 'provisionally_accepted']);
    }

    /**
     * Get application progress percentage
     */
    public function getProgressAttribute(): int
    {
        $steps = [
            'step_personal_completed',
            'step_contact_completed',
            'step_next_of_kin_completed',
            'step_academic_completed',
            'step_programs_completed',
            'step_documents_completed',
            'step_declaration_completed',
        ];
        
        $completed = 0;
        foreach ($steps as $step) {
            if ($this->$step) $completed++;
        }
        
        return round(($completed / count($steps)) * 100);
    }

    /**
     * Check if application is complete (all steps done)
     */
    public function isComplete(): bool
    {
        $requiredSteps = [
            'step_personal_completed',
            'step_contact_completed',
            'step_next_of_kin_completed',
            'step_academic_completed',
            'step_programs_completed',
            'step_documents_completed',
        ];
        
        foreach ($requiredSteps as $step) {
            if (!$this->$step) return false;
        }
        
        return true;
    }

    /**
     * Mark step as completed
     */
    public function markStepCompleted(string $step): void
    {
        $column = "step_{$step}_completed";
        if (in_array($column, [
            'step_personal_completed',
            'step_contact_completed',
            'step_next_of_kin_completed',
            'step_academic_completed',
            'step_programs_completed',
            'step_documents_completed',
            'step_declaration_completed',
        ])) {
            $this->update([$column => true]);
        }
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function personalInfo(): HasOne
    {
        return $this->hasOne(ApplicationPersonalInfo::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(ApplicationContact::class);
    }

    public function nextOfKin(): HasOne
    {
        return $this->hasOne(ApplicationNextOfKin::class);
    }

    public function academic(): HasOne
    {
        return $this->hasOne(ApplicationAcademic::class);
    }

    
    // App/Models/Application.php

public function programChoices()
{
    return $this->hasMany(ApplicationProgramChoice::class, 'application_id');
}


    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function declaration(): HasOne
    {
        return $this->hasOne(ApplicationDeclaration::class);
    }

    public function oLevelSubjects(): HasMany
    {
        return $this->hasManyThrough(
            ApplicationOlevelSubject::class,
            ApplicationAcademic::class,
            'application_id',
            'application_academic_id',
            'id',
            'id'
        );
    }

    public function aLevelSubjects(): HasMany
    {
        return $this->hasManyThrough(
            ApplicationAlevelSubject::class,
            ApplicationAcademic::class,
            'application_id',
            'application_academic_id',
            'id',
            'id'
        );
    }

    /**
     * Get applicant full name
     */
    public function getApplicantNameAttribute(): string
    {
        if ($this->personalInfo) {
            $names = [$this->personalInfo->first_name];
            if ($this->personalInfo->middle_name) {
                $names[] = $this->personalInfo->middle_name;
            }
            $names[] = $this->personalInfo->last_name;
            
            return implode(' ', $names);
        }
        
        return $this->user->name ?? 'N/A';
    }

    /**
     * Get applicant phone
     */
    public function getApplicantPhoneAttribute(): string
    {
        return $this->contact->phone ?? 'N/A';
    }

    /**
     * Get first choice program
     */
    public function getFirstChoiceAttribute()
    {
        if ($this->programChoices && $this->programChoices->firstChoice) {
            return $this->programChoices->firstChoice;
        }
        
        return null;
    }
     public function student()
    {
        return $this->hasOne(Student::class, 'application_id', 'id');
    }

    /**
 * Get the programme selected for this application
 */
public function programme(): BelongsTo
{
    return $this->belongsTo(Programme::class, 'selected_program_id');
}
    
}