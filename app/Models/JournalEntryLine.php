<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
        'metadata'
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'metadata' => 'array'
    ];

    // Relationships
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Methods
    public function getFormattedDebitAttribute()
    {
        return $this->debit > 0 ? 'TZS ' . number_format($this->debit, 2) : '-';
    }

    public function getFormattedCreditAttribute()
    {
        return $this->credit > 0 ? 'TZS ' . number_format($this->credit, 2) : '-';
    }
}