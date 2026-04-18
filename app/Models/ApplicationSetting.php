<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'academic_year',
        'intake',
        'status',
        'opening_date',
        'closing_date',
        'min_education_level',
        'min_division',
        'min_subjects_pass',
        'min_grade',
        'fee_mode',
        'fee_amount',
        'currency',
        'required_documents',
        'enabled_steps',
        'results_entry_mode',
        'manual_verification',
        'recommendation_mode',
        'closed_message',
        'eligibility_message',
        'payment_message',
        'lock_submitted',
        'allow_admin_override',
        'log_changes',
        'version',
        'effective_from',
        'changed_by',
        'is_active',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'closing_date' => 'date',
        'effective_from' => 'date',
        'required_documents' => 'array',
        'enabled_steps' => 'array',
        'fee_amount' => 'decimal:2',
        'manual_verification' => 'boolean',
        'lock_submitted' => 'boolean',
        'allow_admin_override' => 'boolean',
        'log_changes' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the current active application setting
     */
    public static function getActiveSetting()
    {
        return self::where('is_active', true)
            ->where('status', 'OPEN')
            ->whereDate('opening_date', '<=', now())
            ->whereDate('closing_date', '>=', now())
            ->first();
    }

    /**
     * Check if applications are currently open
     */
    public static function isApplicationOpen()
    {
        $setting = self::getActiveSetting();
        return $setting && $setting->status === 'OPEN';
    }

    /**
     * Get application window message
     */
    public static function getWindowMessage()
    {
        $setting = self::getActiveSetting();
        
        if (!$setting) {
            return 'Applications are currently closed.';
        }

        if ($setting->status === 'SUSPENDED') {
            return $setting->closed_message ?? 'Applications are temporarily suspended.';
        }

        if ($setting->status === 'CLOSED') {
            return $setting->closed_message ?? 'Applications are closed.';
        }

        return "Applications are open for {$setting->academic_year} {$setting->intake} intake.";
    }

    /**
     * Programs available for this application cycle
     */
    public function availablePrograms(): HasMany
    {
        return $this->hasMany(ProgramAvailability::class);
    }

    /**
     * User who changed the setting
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Applications submitted under this setting
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}