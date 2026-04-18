<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    protected $fillable = ['module_id', 'name', 'weight', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function scores()
    {
        return $this->hasMany(ComponentScore::class);
    }
}