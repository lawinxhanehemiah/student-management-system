<?php
// app/Models/ResultUploadBatch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultUploadBatch extends Model
{
    use HasFactory;
    
    protected $table = 'result_upload_batches';
    
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'semester',
        'file_name',
        'total_rows',
        'success_rows',
        'failed_rows',
        'status',
        'approved_by',
        'approved_at'
    ];
    
    protected $casts = [
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'approved_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function results()
    {
        return $this->hasMany(StudentResult::class, 'batch_id');
    }
}