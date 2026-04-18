<?php
// app/Models/InvoiceItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'amount',
        'quantity',
        'total',
        'type',
        'category',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'TZS ' . number_format($this->amount, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return 'TZS ' . number_format($this->total, 2);
    }
}