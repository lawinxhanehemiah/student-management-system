<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'academic_years';

    // Mass assignable fields - KULINGANA NA TABLE YAKO
    protected $fillable = [
        'name',
        'status',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    // SCOPES - ADD THESE METHODS
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    // Relationships
    public function programmeFees()
    {
        return $this->hasMany(ProgrammeFee::class);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    // Static method to get active academic years
    public static function getActiveYears()
    {
        return self::where('status', 'active')
                   ->where('is_active', true)
                   ->orderBy('start_date', 'desc')
                   ->get();
    }
}