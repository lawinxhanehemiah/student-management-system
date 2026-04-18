<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'item_code',
        'description',
        'unit',
        'quantity',
        'received_quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'total',
        'metadata'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function grnItems()
    {
        return $this->hasMany(GRNItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(SupplierInvoiceItem::class);
    }

    // Scopes
    public function scopeFullyReceived($query)
    {
        return $query->whereColumn('received_quantity', '>=', 'quantity');
    }

    public function scopePartiallyReceived($query)
    {
        return $query->where('received_quantity', '>', 0)
                     ->whereColumn('received_quantity', '<', 'quantity');
    }

    public function scopeNotReceived($query)
    {
        return $query->where('received_quantity', '<=', 0);
    }

    // Methods
    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    public function getProgressAttribute()
    {
        if ($this->quantity == 0) return 0;
        return round(($this->received_quantity / $this->quantity) * 100, 2);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getDiscountAmountAttribute()
    {
        return $this->subtotal * ($this->discount_rate / 100);
    }

    public function getTaxableAmountAttribute()
    {
        return $this->subtotal - $this->discount_amount;
    }

    public function getTaxAmountAttribute()
    {
        return $this->taxable_amount * ($this->tax_rate / 100);
    }

    public function getTotalAttribute()
    {
        return $this->taxable_amount + $this->tax_amount;
    }

    public function isFullyReceived()
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function updateReceivedQuantity($quantity)
    {
        $this->received_quantity += $quantity;
        $this->save();
    }
}