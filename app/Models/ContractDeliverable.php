<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractDeliverable extends Model
{
    protected $fillable = [
        'contract_id', 'name', 'description', 'due_date',
        'completed_date', 'status', 'value', 'attachments', 'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'value' => 'decimal:2',
        'attachments' => 'array'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}