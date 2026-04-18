<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'programmes';

    // Mass assignable fields
    protected $fillable = [
        'code',
        'name',
        'study_mode',
        'is_active',
        'available_seats',
        'status'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'study_mode' => 'Full Time',
        'is_active' => true,
        'available_seats' => 0,
        'status' => 'active'
    ];

    // Relationships
    public function fees()
    {
        return $this->hasMany(ProgrammeFee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('code', 'like', "%{$search}%");
    }

    // Helper methods
    public function getAvailableSeatsFormattedAttribute()
    {
        return $this->available_seats > 0 ? $this->available_seats : 'Full';
    }

    public function hasAvailableSeats()
    {
        return $this->available_seats > 0;
    }

   // In app/Models/Programme.php
public function modules()
{
    return $this->belongsToMany(Module::class, 'curriculum')
                ->withPivot('year', 'semester', 'credits', 'is_required', 'status')
                ->withTimestamps();
}

public function curriculum()
{
    return $this->hasMany(Curriculum::class);
}

// app/Models/Programme.php
public function department()
{
    return $this->belongsTo(Department::class);
}
}