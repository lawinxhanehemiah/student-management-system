<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'curriculum';

    protected $fillable = [
        'programme_id',
        'module_id',
        'year',
        'semester',
        'credits',
        'is_required',
        'status',
        'academic_year',
        'grading_type',
        'pass_mark',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'credits' => 'decimal:2',
        'pass_mark' => 'decimal:2',
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}