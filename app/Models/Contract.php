<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Contract extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'contract_number', 'title', 'tender_id', 'supplier_id',
        'requisition_id', 'start_date', 'end_date', 'contract_value',
        'status', 'terms', 'documents', 'payment_terms',
        'delivery_terms', 'project_manager', 'created_by',
        'approved_by', 'approved_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
        'terms' => 'array',
        'documents' => 'array',
        'approved_at' => 'datetime'
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliverables()
    {
        return $this->hasMany(ContractDeliverable::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('end_date', '<=', now()->addDays($days))
            ->where('status', 'active');
    }

    public function getRemainingDaysAttribute()
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function getAuditIdentifier(): ?string
    {
        return $this->contract_number;
    }
}