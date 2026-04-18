<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultApproval extends Model
{
    protected $fillable = [
        'result_id', 'user_id', 'action', 'status_from', 'status_to', 'comments'
    ];

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}