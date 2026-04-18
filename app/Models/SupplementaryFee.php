<?php
// app/Models/SupplementaryFee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplementaryFee extends Model
{
    protected $table = 'supplementary_fees';
    
    protected $fillable = [
        'programme_id',
        'academic_year_id',
        'level',
        'semester',
        'total_fee',
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