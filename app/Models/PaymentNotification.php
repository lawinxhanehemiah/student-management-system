<?php
// app/Models/PaymentNotification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentNotification extends Model
{
    protected $table = 'payment_notifications';
    
    protected $fillable = [
        'notification_id', 'payment_type', 'control_number', 'transaction_id',
        'msisdn', 'amount', 'reference', 'raw_data', 'status', 'processing_error',
        'payment_id', 'invoice_id', 'received_at', 'processed_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}