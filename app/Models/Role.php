<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_default',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Users that belong to this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    /**
     * Permissions that belong to this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission($permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Assign permission to role
     */
    public function assignPermission($permissionId)
    {
        return $this->permissions()->syncWithoutDetaching($permissionId);
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission($permissionId)
    {
        return $this->permissions()->detach($permissionId);
    }

    /**
     * Get default role
     */
    public static function getDefaultRole()
    {
        return self::where('is_default', true)->first();
    }
}