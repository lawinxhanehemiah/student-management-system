<?php
// app/Models/HostelFee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostelFee extends Model
{
    protected $table = 'hostel_fees';
    
    protected $fillable = [
        'academic_year_id',
        'programme_id',
        'level',
        'semester',
        'total_fee',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_fee' => 'decimal:2'
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}