<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'reference',
        'status',
        'payment_terms',
        'shipping_address',
        'billing_address',
        'notes',
        'items_summary',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function invoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('order_date', [$from, $to]);
    }

    // Methods
    public function isEditable()
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    public function canBeApproved()
    {
        return $this->status === 'pending_approval';
    }

    public function canBeReceived()
    {
        return in_array($this->status, ['approved', 'ordered', 'partially_received']);
    }

    public function updatePaymentStatus()
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        $this->save();
    }

    public function getProgressAttribute()
    {
        if ($this->total_amount == 0) return 0;
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return 'TZS ' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'TZS ' . number_format($this->balance, 2);
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'secondary',
            'pending_approval' => 'warning',
            'approved' => 'info',
            'ordered' => 'primary',
            'partially_received' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return str_replace('_', ' ', ucwords($this->status));
    }
}