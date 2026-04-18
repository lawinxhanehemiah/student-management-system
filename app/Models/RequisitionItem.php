<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id', 'item_name', 'description', 'quantity',
        'unit', 'estimated_unit_price', 'estimated_total',
        'catalog_number', 'specifications'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total' => 'decimal:2',
        'specifications' => 'array'
    ];

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }
}