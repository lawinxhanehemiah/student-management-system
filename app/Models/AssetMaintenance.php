<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AssetMaintenance extends Model
{
    use Auditable;

    protected $fillable = [
        'asset_id', 'maintenance_date', 'type', 'description',
        'cost', 'performed_by', 'next_maintenance_date',
        'notes', 'metadata', 'created_by'
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
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

    public function scopeUpcoming($query)
    {
        return $query->where('next_maintenance_date', '>=', now())
                     ->orderBy('next_maintenance_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now());
    }
}