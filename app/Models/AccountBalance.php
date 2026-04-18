<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    use HasFactory;

    protected $table = 'account_balances';

    protected $fillable = [
        'account_id',
        'balance_date',
        'opening_balance',
        'total_debit',
        'total_credit',
        'closing_balance',
        'metadata'
    ];

    protected $casts = [
        'balance_date' => 'date',
        'opening_balance' => 'decimal:2',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Methods
    public function getFormattedOpeningAttribute()
    {
        return 'TZS ' . number_format($this->opening_balance, 2);
    }

    public function getFormattedClosingAttribute()
    {
        return 'TZS ' . number_format($this->closing_balance, 2);
    }
}