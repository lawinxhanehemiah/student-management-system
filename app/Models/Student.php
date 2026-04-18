<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',              // FK → users
        'application_id',       // FK → applications (default 0)
        'registration_number',  // Unique registration number
        'programme_id',         // FK → programmes
        
        'study_mode',           // full_time, part_time, distance
        'intake',               // March, September
        'current_level',        // 1-6 (Year of study)
        'current_semester',     // 1 or 2
        'academic_year_id',     // FK → academic_years
        'status',               // active, suspended, graduated, discontinued
        'guardian_name',
        'guardian_phone',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast
     */
    protected $casts = [
        'current_level' => 'integer',
        'current_semester' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Default values for attributes
     */
    protected $attributes = [
        'application_id' => 0,
        'current_level' => 1,
        'current_semester' => 1,
        'status' => 'active',
    ];

    /**
     * Relationship: Student belongs to a User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Student belongs to a Programme
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function currentAcademicYear()
{
    return $this->belongsTo(AcademicYear::class, 'academic_year_id');
}

    /**
     * Relationship: Student belongs to a Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relationship: Student belongs to an Academic Year
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relationship: Student belongs to an Application (optional)
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relationship: Student has many Invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relationship: Student has many Fee Transactions
     */
    public function feeTransactions(): HasMany
    {
        return $this->hasMany(FeeTransaction::class);
    }

    /**
     * Helper: Get full name from User
     */
    public function getFullNameAttribute(): string
    {
        if ($this->user) {
            $names = [$this->user->first_name];
            if ($this->user->middle_name) {
                $names[] = $this->user->middle_name;
            }
            $names[] = $this->user->last_name;

            return implode(' ', $names);
        }

        return '';
    }

    /**
     * Get full name with registration number
     */
    public function getFullNameWithRegAttribute(): string
    {
        return $this->full_name . ' (' . $this->registration_number . ')';
    }

    /**
     * Get current level as text
     */
    public function getCurrentLevelTextAttribute(): string
    {
        if (!$this->current_level) {
            return '-';
        }
        
        $suffix = match ($this->current_level) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th'
        };
        
        return $this->current_level . $suffix . ' Year';
    }

    /**
     * Get current semester as text
     */
    public function getCurrentSemesterTextAttribute(): string
    {
        if (!$this->current_semester) {
            return '-';
        }
        
        return 'Semester ' . $this->current_semester;
    }

    /**
     * Get study mode as text
     */
    public function getStudyModeTextAttribute(): string
    {
        return match ($this->study_mode) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'distance' => 'Distance Learning',
            default => ucfirst(str_replace('_', ' ', $this->study_mode))
        };
    }

    /**
     * Get status as badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'suspended' => 'warning',
            'graduated' => 'info',
            'discontinued' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status as text
     */
    public function getStatusTextAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Check if student is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if student is graduated
     */
    public function isGraduated(): bool
    {
        return $this->status === 'graduated';
    }

    /**
     * Check if student is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if student is discontinued
     */
    public function isDiscontinued(): bool
    {
        return $this->status === 'discontinued';
    }

    /**
     * Get total paid fees
     */
    public function getTotalPaidFeesAttribute(): float
    {
        return $this->feeTransactions()
            ->where('status', 'completed')
            ->sum('amount') ?? 0;
    }

    /**
     * Get total invoice amount
     */
    public function getTotalInvoicedAttribute(): float
    {
        return $this->invoices()
            ->where('status', 'pending')
            ->sum('total_amount') ?? 0;
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return $this->invoices()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('balance') ?? 0;
    }

    /**
     * Get current invoice for current academic year
     */
    public function getCurrentInvoiceAttribute()
    {
        return $this->invoices()
            ->where('academic_year_id', $this->academic_year_id)
            ->where('invoice_type', 'tuition')
            ->first();
    }

    /**
     * Check if student has any pending invoices
     */
    public function hasPendingInvoices(): bool
    {
        return $this->invoices()
            ->where('payment_status', 'unpaid')
            ->exists();
    }

    /**
     * Check if student has overdue invoices
     */
    public function hasOverdueInvoices(): bool
    {
        return $this->invoices()
            ->where('payment_status', 'unpaid')
            ->where('due_date', '<', now())
            ->exists();
    }

    /**
     * Scope: Active students
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Graduated students
     */
    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    /**
     * Scope: Suspended students
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope: Discontinued students
     */
    public function scopeDiscontinued($query)
    {
        return $query->where('status', 'discontinued');
    }

    /**
     * Scope: Students by programme
     */
    public function scopeByProgramme($query, $programmeId)
    {
        return $query->where('programme_id', $programmeId);
    }

    /**
     * Scope: Students by course
     */
    

    /**
     * Scope: Students by academic year
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope: Students by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('current_level', $level);
    }

    /**
     * Scope: Students by study mode
     */
    public function scopeByStudyMode($query, $mode)
    {
        return $query->where('study_mode', $mode);
    }

    /**
     * Scope: Students by intake
     */
    public function scopeByIntake($query, $intake)
    {
        return $query->where('intake', $intake);
    }

    /**
     * Scope: Students with pending invoices
     */
    public function scopeWithPendingInvoices($query)
    {
        return $query->whereHas('invoices', function($q) {
            $q->where('payment_status', 'unpaid');
        });
    }

    /**
     * Scope: Students with overdue invoices
     */
    public function scopeWithOverdueInvoices($query)
    {
        return $query->whereHas('invoices', function($q) {
            $q->where('payment_status', 'unpaid')
              ->where('due_date', '<', now());
        });
    }

    /**
     * Scope: Search by name or registration number
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('registration_number', 'like', "%{$search}%")
              ->orWhereHas('user', function($userQuery) use ($search) {
                  $userQuery->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Before creating a student, ensure registration number is set
        static::creating(function ($student) {
            if (empty($student->registration_number)) {
                throw new \Exception('Registration number is required for students.');
            }
        });

        // After creating a student, log activity
        static::created(function ($student) {
            \Log::info('New student created', [
                'student_id' => $student->id,
                'registration_number' => $student->registration_number,
                'user_id' => $student->user_id
            ]);
        });

        // Before updating student status, log the change
        static::updating(function ($student) {
            if ($student->isDirty('status')) {
                \Log::info('Student status changed', [
                    'student_id' => $student->id,
                    'old_status' => $student->getOriginal('status'),
                    'new_status' => $student->status,
                    'changed_by' => auth()->id()
                ]);
            }
        });
    }

    public function creditNotes()
{
    return $this->hasMany(CreditNote::class);
}

public function refunds()
{
    return $this->hasMany(Refund::class);
}
public function totalCreditNotes()
{
    return $this->creditNotes()->sum('remaining_amount');
}

// Add these relationships if missing

public function payments()
{
    return $this->hasMany(Payment::class);
}
/**
     * Get GPA status (Good/Pass/Probation)
     */
    public function getGpaStatusAttribute()
    {
        $gpa = $this->cumulative_gpa ?? 0;
        
        if ($gpa >= 2.0) {
            return ['text' => 'Good', 'class' => 'success', 'icon' => 'check-circle'];
        } elseif ($gpa >= 1.5) {
            return ['text' => 'Pass', 'class' => 'warning', 'icon' => 'chart-line'];
        } elseif ($gpa > 0) {
            return ['text' => 'Probation', 'class' => 'danger', 'icon' => 'exclamation-triangle'];
        }
        
        return ['text' => 'N/A', 'class' => 'secondary', 'icon' => 'minus-circle'];
    }
    
    /**
     * Get semester display
     */
    public function getSemesterDisplayAttribute()
    {
        return "Semester {$this->current_semester}";
    }
    
    /**
     * Get level display
     */
    public function getLevelDisplayAttribute()
    {
        return "Year {$this->current_level}";
    }
    
   
    
    /**
     * Get initials for avatar
     */
    public function getInitialsAttribute()
    {
        $first = strtoupper(substr($this->user->first_name ?? 'S', 0, 1));
        $last = strtoupper(substr($this->user->last_name ?? 'T', 0, 1));
        return $first . $last;
    }
    
    /**
     * Get fee status display
     */
    public function getFeeStatusAttribute()
    {
        $balance = $this->invoices()->sum('balance');
        return [
            'cleared' => $balance <= 0,
            'balance' => $balance,
            'display' => $balance <= 0 ? 'Cleared' : 'Outstanding',
            'class' => $balance <= 0 ? 'success' : 'danger'
        ];
    }

    public function results()
{
    return $this->hasMany(Result::class);
}


}