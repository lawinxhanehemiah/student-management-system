<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type', // e.g., danger, warning, info, success
        'active', // 1 = active, 0 = inactive
    ];
}
