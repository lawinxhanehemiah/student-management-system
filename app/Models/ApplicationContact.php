<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationContact extends Model
{
    use HasFactory;

    protected $table = 'application_contacts'; // Ongeza hii kama haipo

    protected $fillable = [
        'application_id',
        'phone',
        'phone_alternative',
        'email',
        'region',
        'district',
    ];

    // Ongeza guarded kwa usalama
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}