<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Grading\GradingConfigValidator;
use Illuminate\Support\Facades\Log;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'academic_years';

    protected $fillable = [
        'name',
        'status',
        'start_date',
        'is_locked',      // Unayo tayari!
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',  // Add this cast
    ];

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    // NEW: Scope for unlocked years only
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    // Relationships
    public function programmeFees()
    {
        return $this->hasMany(ProgrammeFee::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * NEW: Lock academic year - prevents grading configuration changes
     */
    public function lock($userId): array
    {
        // Check if already locked
        if ($this->is_locked) {
            return [
                'success' => false,
                'message' => 'Academic year is already locked'
            ];
        }
        
        // Validate configuration before locking
        $validator = new GradingConfigValidator($this);
        $validation = $validator->validate();
        
        if (!$validation['is_valid']) {
            return [
                'success' => false,
                'message' => 'Cannot lock academic year: Configuration errors found',
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings']
            ];
        }
        
        // Lock the year
        $this->update([
            'is_locked' => true,
        ]);
        
        // Log the lock action
        Log::info('Academic year locked', [
            'academic_year_id' => $this->id,
            'academic_year_name' => $this->name,
            'locked_by' => $userId
        ]);
        
        // Store lock record in a separate table for audit
        AcademicYearLock::create([
            'academic_year_id' => $this->id,
            'locked_by' => $userId,
            'locked_at' => now(),
            'validation_result' => json_encode($validation)
        ]);
        
        return [
            'success' => true,
            'message' => 'Academic year locked successfully',
            'validation' => $validation
        ];
    }
    
    /**
     * NEW: Unlock academic year (admin only, with reason)
     */
    public function unlock($userId, $reason = null): array
    {
        if (!$this->is_locked) {
            return [
                'success' => false,
                'message' => 'Academic year is not locked'
            ];
        }
        
        // Check if results have been published
        $hasPublishedResults = StudentResult::where('academic_year_id', $this->id)
            ->where('workflow_status', 'published')
            ->exists();
            
        if ($hasPublishedResults) {
            return [
                'success' => false,
                'message' => 'Cannot unlock: Results have been published for this academic year'
            ];
        }
        
        $this->update([
            'is_locked' => false,
        ]);
        
        Log::warning('Academic year UNLOCKED', [
            'academic_year_id' => $this->id,
            'academic_year_name' => $this->name,
            'unlocked_by' => $userId,
            'reason' => $reason
        ]);
        
        AcademicYearLock::where('academic_year_id', $this->id)
            ->whereNull('unlocked_at')
            ->update([
                'unlocked_by' => $userId,
                'unlocked_at' => now(),
                'unlock_reason' => $reason
            ]);
        
        return [
            'success' => true,
            'message' => 'Academic year unlocked successfully'
        ];
    }
    
    /**
     * NEW: Check if configuration can be modified
     */
    public function canModifyConfig(): bool
    {
        // If not locked, can modify
        if (!$this->is_locked) {
            return true;
        }
        
        // If locked, check if any results exist
        $hasResults = StudentResult::where('academic_year_id', $this->id)->exists();
        
        // Locked with no results = can still modify (not yet used)
        // Locked with results = cannot modify
        return !$hasResults;
    }
    
    /**
     * NEW: Get current/latest academic year
     */
    public static function getCurrent(): ?self
    {
        return self::where('status', 'active')
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->first();
    }
    
    /**
     * NEW: Get active academic years
     */
    public static function getActiveYears()
    {
        return self::where('status', 'active')
                   ->where('is_active', true)
                   ->orderBy('start_date', 'desc')
                   ->get();
    }
}