<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectsNecta extends Model
{
    protected $table = 'subjects_necta';
    
    protected $fillable = [
        'subject_name',
        'code',
        'is_core',
        'is_science',
        'is_commercial',
        'is_arts'
    ];
    
    /**
     * Get the grades for this subject
     */
    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class, 'subject_id');
    }
}