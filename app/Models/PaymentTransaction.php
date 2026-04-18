<?php
// app/Models/PaymentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'control_number_id',
        'transaction_id',
        'merchant_request_id',
        'checkout_request_id',
        'payment_method',
        'amount',
        'phone_number',
        'status',
        'request_data',
        'response_data',
        'failure_reason',
        'completed_at',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_METHODS = [
        'mpesa' => 'M-Pesa',
        'tigo_pesa' => 'Tigo Pesa',
        'airtel_money' => 'Airtel Money',
        'nmb_bank' => 'NMB Bank',
        'crdb_bank' => 'CRDB Bank',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'other' => 'Other'
    ];

    // ============ RELATIONSHIPS ============
    public function controlNumber()
    {
        return $this->belongsTo(ControlNumber::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============ SCOPES ============
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)  // ← HII NDIO ILIKOSA!
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeTimeout($query)
    {
        return $query->where('status', self::STATUS_TIMEOUT);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    // ============ ACCESSORS ============
    public function getMethodNameAttribute()
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    public function getStatusNameAttribute()
    {
        return ucfirst($this->status);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            'timeout' => 'secondary',
            'refunded' => 'info'
        ];
        
        $class = $badges[$this->status] ?? 'secondary';
        
        return "<span class='badge bg-{$class}'>{$this->status_name}</span>";
    }

    // ============ HELPERS ============
    public function markAsCompleted($transactionId = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        
        return $this->save();
    }

    public function markAsFailed($reason = null)
    {
        $this->status = self::STATUS_FAILED;
        $this->failure_reason = $reason;
        return $this->save();
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }
}