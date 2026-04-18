<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ IMPORTANT: This is the correct namespace
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Auditable, SoftDeletes; // ✅ Now correct

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'registration_number',
        'phone',
        'gender',
        'profile_photo',
        'department_id',
        'status', 
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // ✅ For SoftDeletes
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // User → Student (1–1)
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function department()
{
    return $this->belongsTo(Department::class, 'department_id');
}

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['superadmin', 'admin']);
    }

    public function shouldAudit(string $action): bool
    {
        // Don't audit 'restored' for User model 
        if ($action === 'restored') {
            return false;
        }
        
        return true;
    }

    /**
     * Get audit identifier for this model
     */
    public function getAuditIdentifier(): ?string
    {
        return $this->email ?? $this->first_name . ' ' . $this->last_name ?? "User #{$this->id}";
    }

    public function scopeOrderByName($query)
    {
        return $query->orderBy('first_name')->orderBy('last_name');
    }

    public function students()
{
    return $this->hasMany(Student::class, 'department_id', 'department_id');
}

public function scopeByDepartment($query, $departmentId)
{
    return $query->where('department_id', $departmentId);
}

public function scopeByRole($query, $roleName)
{
    return $query->whereHas('roles', function($q) use ($roleName) {
        $q->where('name', $roleName);
    });
}
/**
 * Get the department this user belongs to
 */


/**
 * Get students in the same department (for HOD)
 */
public function departmentStudents()
{
    return Student::whereHas('programme', function($q) {
        $q->where('department_id', $this->department_id);
    });
}

/**
 * Get pending approvals count
 */
public function getPendingApprovalsCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get pending results count
 */
public function getPendingResultsCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get pending requisitions count
 */
public function getPendingRequisitionsCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get pending leave requests count
 */
public function getPendingLeaveCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get pending promotions count
 */
public function getPendingPromotionCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get pending budget approvals count
 */
public function getPendingBudgetCountAttribute()
{
    // Example - adjust based on your actual models
    return 0;
}

/**
 * Get profile photo URL
 */
public function getProfilePhotoUrlAttribute()
{
    if ($this->profile_photo) {
        // Ikiwa profile_photo ni full path (kama inaanza na http)
        if (filter_var($this->profile_photo, FILTER_VALIDATE_URL)) {
            return $this->profile_photo;
        }
        
        // Ikiwa imehifadhiwa kwenye storage
        return asset('storage/profile_photos/' . $this->profile_photo);
    }
    
    // Default avatar (unaweza kubadilisha)
    return 'https://ui-avatars.com/api/?name=' . urlencode($this->first_name . ' ' . $this->last_name) . '&color=7F9CF5&background=EBF4FF';
}

}