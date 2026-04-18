<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class AssetCategory extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name', 'code', 'description', 'depreciation_method',
        'default_useful_life_years', 'default_salvage_value_percentage',
        'is_active', 'metadata', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_salvage_value_percentage' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDepreciationMethodNameAttribute()
    {
        $methods = [
            'straight_line' => 'Straight Line',
            'declining_balance' => 'Declining Balance',
            'none' => 'No Depreciation'
        ];
        return $methods[$this->depreciation_method] ?? $this->depreciation_method;
    }
}