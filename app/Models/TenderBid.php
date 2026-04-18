<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderBid extends Model
{
    protected $fillable = [
        'bid_number', 'tender_id', 'supplier_id', 'bid_amount',
        'technical_proposal', 'documents', 'status',
        'technical_score', 'financial_score', 'total_score',
        'evaluation_comments', 'evaluated_by', 'evaluated_at'
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'technical_score' => 'decimal:2',
        'financial_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'documents' => 'array',
        'evaluated_at' => 'datetime'
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }
}