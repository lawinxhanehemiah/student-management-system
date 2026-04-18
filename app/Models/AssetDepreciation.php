<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AssetDepreciation extends Model
{
    use Auditable;

    protected $fillable = [
        'asset_id', 'depreciation_date', 'period_number',
        'period_depreciation', 'accumulated_depreciation',
        'book_value', 'method', 'metadata', 'created_by'
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'period_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}