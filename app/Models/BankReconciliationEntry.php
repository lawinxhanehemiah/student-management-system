<?php
// app/Models/BankReconciliationEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankReconciliationEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_reconciliation_id',
        'bank_transaction_id',
        'matched',
        'adjustment_type', // missing_in_bank, missing_in_system, wrong_amount
        'notes'
    ];

    protected $casts = [
        'matched' => 'boolean'
    ];

    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    public function transaction()
    {
        return $this->belongsTo(BankTransaction::class, 'bank_transaction_id');
    }
}