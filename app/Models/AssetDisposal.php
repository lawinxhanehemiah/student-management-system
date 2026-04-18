<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class AssetDisposal extends Model
{
    use Auditable;

    protected $fillable = [
        'asset_id', 'disposal_date', 'disposal_method',
        'disposal_amount', 'book_value_at_disposal', 'gain_loss',
        'reason', 'authorized_by', 'metadata', 'created_by'
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'disposal_amount' => 'decimal:2',
        'book_value_at_disposal' => 'decimal:2',
        'gain_loss' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDisposalMethodNameAttribute()
    {
        $methods = [
            'sold' => 'Sold',
            'scrapped' => 'Scrapped',
            'donated' => 'Donated',
            'lost' => 'Lost',
            'stolen' => 'Stolen',
            'other' => 'Other'
        ];
        return $methods[$this->disposal_method] ?? $this->disposal_method;
    }
}