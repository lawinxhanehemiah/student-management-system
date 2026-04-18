<?php
// app/Models/RepeatModuleFee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepeatModuleFee extends Model
{
    protected $table = 'repeat_module_fees';
    
    protected $fillable = [
        'programme_id',
        'academic_year_id',
        'level',
        'semester',
        'total_fee',      // ONLY TOTAL FEE
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_fee' => 'decimal:2'
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}