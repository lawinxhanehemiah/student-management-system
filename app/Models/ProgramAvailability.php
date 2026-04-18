<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAvailability extends Model
{
    protected $fillable = [
        'application_setting_id',
        'program_id',
        'is_active',
        'intake_allowed',
        'capacity',
        'min_requirements',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'min_requirements' => 'array',
    ];

    /**
     * Application setting
     */
    public function applicationSetting(): BelongsTo
    {
        return $this->belongsTo(ApplicationSetting::class);
    }

    /**
     * Program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Check if program has available slots
     */
    public function hasAvailableSlots(): bool
    {
        if ($this->capacity === null) {
            return true; // No capacity limit
        }

        $currentApplications = Application::where('first_choice_program_id', $this->program_id)
            ->orWhere('second_choice_program_id', $this->program_id)
            ->orWhere('third_choice_program_id', $this->program_id)
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'ACCEPTED'])
            ->count();

        return $currentApplications < $this->capacity;
    }
}