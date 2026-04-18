<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationOlevelSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_academic_id',
        'subject',
        'grade',
        'points'
    ];

    public function academic()
    {
        return $this->belongsTo(ApplicationAcademic::class, 'application_academic_id');
    }
}
