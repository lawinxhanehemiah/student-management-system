<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Tender extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'tender_number', 'title', 'description', 'requisition_id',
        'type', 'status', 'published_date', 'closing_date',
        'evaluation_date', 'estimated_value', 'documents',
        'eligibility_criteria', 'evaluation_criteria',
        'terms_and_conditions', 'created_by', 'approved_by', 'approved_at'
    ];

    protected $casts = [
        'published_date' => 'date',
        'closing_date' => 'date',
        'evaluation_date' => 'date',
        'estimated_value' => 'decimal:2',
        'documents' => 'array',
        'eligibility_criteria' => 'array',
        'evaluation_criteria' => 'array',
        'approved_at' => 'datetime'
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function bids()
    {
        return $this->hasMany(TenderBid::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOpen($query)
    {
        return $query->where('closing_date', '>=', now());
    }

    public function getLowestBidAttribute()
    {
        return $this->bids()->where('status', 'submitted')->min('bid_amount');
    }

    public function getAuditIdentifier(): ?string
    {
        return $this->tender_number;
    }
}