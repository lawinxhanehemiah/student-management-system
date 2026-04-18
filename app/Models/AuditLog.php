<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Finance\Audit\AuditController;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'model_type',
        'model_id',
        'model_identifier',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'description',
        'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array'
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

    public function auditable()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }
}