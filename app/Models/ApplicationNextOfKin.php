<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationNextOfKin extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'guardian_name',
        'relationship',
        'guardian_phone',
        
        
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
