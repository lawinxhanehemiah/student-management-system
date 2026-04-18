<?php
// app/Models/BankTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BankTransaction extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'bank_account_id',
        'transaction_number',
        'transaction_date',
        'transaction_type', // deposit, withdrawal, transfer, fee, interest
        'amount',
        'balance_before',
        'balance_after',
        'reference_type', // payment, invoice, reconciliation, etc
        'reference_id',
        'description',
        'reference_type',
        'reference_id',
        'status', // pending, completed, failed, reconciled
        'reconciled_at',
        'reconciled_by',
        'metadata',
        'created_by'
        
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'reconciled_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
    

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

    public function reconciler()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge bg-warning',
            'completed' => 'badge bg-success',
            'failed' => 'badge bg-danger',
            'reconciled' => 'badge bg-info'
        ];

        return '<span class="' . ($badges[$this->status] ?? 'badge bg-secondary') . '">' 
            . ucfirst($this->status) . '</span>';
    }
}