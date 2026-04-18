<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'head_of_department',
        'email',
        'phone',
        'description',
        'budget',
        'is_active',
        'created_by',
        'updated_by',
        'metadata'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Get the users in this department
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Get the creator of this department
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the last updater of this department
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the head of department user
     */
    public function head()
    {
        return User::where('department_id', $this->id)
            ->whereHas('roles', function($q) {
                $q->where('name', 'Head_of_Department');
            })
            ->first();
    }

    public function modules()
{
    return $this->hasMany(Module::class);
}
}