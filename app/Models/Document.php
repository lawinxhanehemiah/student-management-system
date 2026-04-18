<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_id',
        'document_type',
        'original_name',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'verified_at' => 'datetime'
    ];

    // Document types
    const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';
    const TYPE_FORM_IV = 'form_iv_certificate';
    const TYPE_FORM_VI = 'form_vi_certificate';
    const TYPE_DIPLOMA = 'diploma_certificate';
    const TYPE_DEGREE = 'degree_certificate';
    const TYPE_PASSPORT_PHOTO = 'passport_photo';
    const TYPE_NATIONAL_ID = 'national_id';
    const TYPE_OTHER = 'other';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application associated with the document.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the verifier user.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if document is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if document is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if document is rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get document type label.
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_BIRTH_CERTIFICATE => 'Birth Certificate',
            self::TYPE_FORM_IV => 'Form IV Certificate',
            self::TYPE_FORM_VI => 'Form VI Certificate',
            self::TYPE_DIPLOMA => 'Diploma Certificate',
            self::TYPE_DEGREE => 'Degree Certificate',
            self::TYPE_PASSPORT_PHOTO => 'Passport Photo',
            self::TYPE_NATIONAL_ID => 'National ID',
            self::TYPE_OTHER => 'Other Document',
        ];

        return $labels[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Scope for user's documents.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for application documents.
     */
    public function scopeForApplication($query, $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }
}