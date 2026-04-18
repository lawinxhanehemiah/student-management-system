<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocType extends Model
{
    protected $table = 'document_types';
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_mandatory',
        'is_active',
        'sort_order',
    ];
    
    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type', 'code');
    }
}