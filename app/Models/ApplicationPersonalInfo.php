<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPersonalInfo extends Model
{
    protected $fillable = [
        'application_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'nationality',
        'marital_status',
        
    ];

    protected $casts = [
        
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getFullNameAttribute(): string
    {
        $names = [$this->first_name];
        if ($this->middle_name) $names[] = $this->middle_name;
        $names[] = $this->last_name;
        
        return implode(' ', $names);
    }
}