<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLog extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'action',
        'changes',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * User who performed action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create log entry
     */
    public static function log($applicationId, $action, $changes = null, $notes = null, $userId = null)
    {
        return self::create([
            'application_id' => $applicationId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'changes' => $changes,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}