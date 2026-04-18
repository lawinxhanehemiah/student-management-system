<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'group',
        'description',
    ];

    /**
     * Roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Get all permissions grouped by module
     */
    public static function getGroupedPermissions()
    {
        return self::orderBy('module')
            ->orderBy('group')
            ->get()
            ->groupBy('module');
    }

    /**
     * Get permission modules
     */
    public static function getModules()
    {
        return self::distinct('module')->pluck('module')->toArray();
    }
}