<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'supplier_invoice_items';

    protected $fillable = [
        'supplier_invoice_id',
        'purchase_order_item_id',
        'grn_item_id',
        'description',
        'quantity',
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
    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function grnItem()
    {
        return $this->belongsTo(GRNItem::class);
    }

    // Scopes
    public function scopeLinkedToPO($query)
    {
        return $query->whereNotNull('purchase_order_item_id');
    }

    public function scopeLinkedToGRN($query)
    {
        return $query->whereNotNull('grn_item_id');
    }

    // Methods
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

    public function hasPOReference()
    {
        return !is_null($this->purchase_order_item_id);
    }

    public function hasGRNReference()
    {
        return !is_null($this->grn_item_id);
    }
}