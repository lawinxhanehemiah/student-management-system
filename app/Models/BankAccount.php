<?php
// app/Models/BankAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'branch',
        'swift_code',
        'currency',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_default',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array'
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    public function getDefaultBadgeAttribute()
    {
        return $this->is_default 
            ? '<span class="badge bg-info">Default</span>'
            : '';
    }

    public function updateBalance($amount, $type)
    {
        if ($type === 'credit') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        $this->save();
    }
}