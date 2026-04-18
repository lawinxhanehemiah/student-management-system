<?php
// app/Models/PaymentAttempt.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'payment_id', 'attempt_number', 'status', 'request_data',
        'response_data', 'error_message', 'error_code', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}