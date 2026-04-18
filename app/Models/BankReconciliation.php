<?php
// app/Models/BankReconciliation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id',
        'reconciliation_number',
        'statement_date',
        'statement_balance',
        'system_balance',
        'difference',
        'start_date',
        'end_date',
        'status', // draft, in_progress, completed, cancelled
        'notes',
        'adjustments',
        'completed_at',
        'completed_by',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'statement_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'adjustments' => 'array',
        'completed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function entries()
    {
        return $this->hasMany(BankReconciliationEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge bg-secondary',
            'in_progress' => 'badge bg-warning',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger'
        ];

        return '<span class="' . ($badges[$this->status] ?? 'badge bg-secondary') . '">' 
            . ucfirst($this->status) . '</span>';
    }
}