<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Finance\Audit\AuditController;

class TransactionLog extends Model
{
    protected $fillable = [
        'transaction_number',
        'reference_type',
        'reference_id',
        'transaction_type',
        'amount',
        'currency',
        'status',
        'before_status',
        'after_status',
        'user_id',
        'user_name',
        'ip_address',
        'description',
        'metadata',
        'transaction_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'before_status' => 'array',
        'after_status' => 'array',
        'metadata' => 'array',
        'transaction_date' => 'datetime'
    ];

    protected static function booted()
    {
        // ✅ AUTOMATIC CACHE INVALIDATION
        static::created(function () {
            AuditController::clearCache();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}