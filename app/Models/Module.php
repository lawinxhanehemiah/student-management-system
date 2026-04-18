<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'department_id',
        'is_active',
        'nta_level',
        'default_credits',
        'pass_mark',
        'type',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_credits' => 'decimal:2',
        'pass_mark' => 'decimal:2',
        'nta_level' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function programmes()
    {
        return $this->belongsToMany(Programme::class, 'curriculum')
                    ->withPivot('year', 'semester', 'credits', 'is_required', 'status', 'academic_year', 'grading_type', 'pass_mark')
                    ->withTimestamps();
    }
}