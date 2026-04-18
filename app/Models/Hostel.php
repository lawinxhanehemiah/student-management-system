<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'capacity',
        'gender',
        'fee_per_semester',
        'fee_per_year',
        'location',
        'warden_name',
        'warden_phone',
        'status',
        'description'
    ];

    protected $casts = [
        'fee_per_semester' => 'decimal:2',
        'fee_per_year' => 'decimal:2',
        'capacity' => 'integer',
        'status' => 'boolean'
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function payments()
    {
        return $this->hasMany(ControlNumber::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function getAvailableRoomsAttribute()
    {
        $allocated = $this->allocations()
            ->where('status', 'active')
            ->count();
        return $this->capacity - $allocated;
    }
}