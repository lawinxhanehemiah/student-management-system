<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRNItem extends Model
{
    use HasFactory;

    protected $table = 'grn_items';

    protected $fillable = [
        'goods_received_note_id',
        'purchase_order_item_id',
        'quantity_received',
        'quantity_accepted',
        'quantity_rejected',
        'rejection_reason',
        'batch_number',
        'expiry_date',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'quantity_accepted' => 'decimal:2',
        'quantity_rejected' => 'decimal:2',
        'expiry_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function goodsReceivedNote()
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function invoiceItem()
    {
        return $this->hasOne(SupplierInvoiceItem::class);
    }

    // Scopes
    public function scopeAccepted($query)
    {
        return $query->where('quantity_accepted', '>', 0);
    }

    public function scopeRejected($query)
    {
        return $query->where('quantity_rejected', '>', 0);
    }

    public function scopeHasBatch($query)
    {
        return $query->whereNotNull('batch_number');
    }

    // Methods
    public function getAcceptanceRateAttribute()
    {
        if ($this->quantity_received == 0) return 0;
        return round(($this->quantity_accepted / $this->quantity_received) * 100, 2);
    }

    public function getRejectionRateAttribute()
    {
        if ($this->quantity_received == 0) return 0;
        return round(($this->quantity_rejected / $this->quantity_received) * 100, 2);
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isAboutToExpire($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }
}