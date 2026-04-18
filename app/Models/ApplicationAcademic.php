<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationAcademic extends Model
{
    protected $fillable = [
        'application_id',
        'csee_school',
        'csee_school_address',
        'csee_index_number',
        'csee_year',
        'csee_division',
        'csee_points',
        'csee_examination_body',
        'acsee_school',
        'acsee_school_address',
        'acsee_index_number',
        'acsee_year',
        'acsee_principal_passes',
        'acsee_combination',
        'acsee_examination_body',
        'diploma_institution',
        'diploma_programme',
        'diploma_year',
        'diploma_class',
        'diploma_gpa',
        'degree_institution',
        'degree_programme',
        'degree_year',
        'degree_class',
        'degree_gpa',
    ];

    protected $casts = [
        'csee_year' => 'integer',
        'acsee_year' => 'integer',
        'diploma_year' => 'integer',
        'degree_year' => 'integer',
        'csee_points' => 'integer',
        'acsee_principal_passes' => 'integer',
        'diploma_gpa' => 'decimal:2',
        'degree_gpa' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function oLevelSubjects(): HasMany
    {
        return $this->hasMany(ApplicationOlevelSubject::class);
    }

    public function aLevelSubjects(): HasMany
    {
        return $this->hasMany(ApplicationAlevelSubject::class);
    }

    public function hasALevel(): bool
    {
        return !empty($this->acsee_index_number);
    }

    public function getExaminationLevelAttribute(): string
    {
        return $this->hasALevel() ? 'ACSEE' : 'CSEE';
    }
}