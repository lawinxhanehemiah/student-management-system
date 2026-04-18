<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'file_type',
        'verification_status',
        'verification_notes',
        'verified_by',
        'verified_at'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
