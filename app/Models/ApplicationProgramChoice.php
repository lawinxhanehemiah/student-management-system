<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationProgramChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'first_choice_program_id',
        'second_choice_program_id',
        'third_choice_program_id',
        'study_mode',
        'sponsorship',
        'sponsor_name',
        'sponsor_phone',
        'information_source',
        'information_source_other'
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    public function program()
{
    return $this->belongsTo(Programme::class, 'first_choice_program_id');
}

}
