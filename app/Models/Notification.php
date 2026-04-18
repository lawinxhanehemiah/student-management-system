<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'read',
        'data',
    ];
    
    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['read' => true]);
    }
    
    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }
}