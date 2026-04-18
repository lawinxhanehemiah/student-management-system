<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationDeclaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'confirm_information',
        'accept_terms',
        'confirm_documents',
        'allow_data_sharing',
        'declared_at',
        'signature_path',
        'ip_address',
        'user_agent'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
