<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Carbon\Carbon;

class Asset extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'asset_tag', 'name', 'serial_number', 'category_id',
        'department_id', 'supplier_id', 'purchase_order_id',
        'purchase_date', 'purchase_cost', 'current_value',
        'salvage_value', 'useful_life_years', 'warranty_expiry',
        'location', 'status', 'assigned_to', 'description',
        'specifications', 'metadata', 'created_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'specifications' => 'array',
        'metadata' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function disposals()
    {
        return $this->hasMany(AssetDisposal::class);
    }

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class);
    }

    public function calculateAnnualDepreciation()
    {
        if ($this->salvage_value >= $this->purchase_cost) {
            return 0;
        }
        return ($this->purchase_cost - $this->salvage_value) / $this->useful_life_years;
    }

    public function calculateMonthlyDepreciation()
    {
        return $this->calculateAnnualDepreciation() / 12;
    }

    public function getAgeInYears()
    {
        return Carbon::parse($this->purchase_date)->age;
    }

    public function getRemainingLifeYears()
    {
        return max(0, $this->useful_life_years - $this->getAgeInYears());
    }

    public function getDepreciationPercentage()
    {
        if ($this->useful_life_years == 0) return 0;
        return min(100, ($this->getAgeInYears() / $this->useful_life_years) * 100);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isDisposed()
    {
        return $this->status === 'disposed';
    }

    public function isUnderMaintenance()
    {
        return $this->status === 'under_maintenance';
    }

    public function getAuditIdentifier(): ?string
    {
        return $this->asset_tag . ' - ' . $this->name;
    }
}