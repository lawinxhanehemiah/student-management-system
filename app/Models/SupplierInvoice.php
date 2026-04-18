<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoice extends Model
{
    use SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Payment status constants
    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';
    const PAYMENT_STATUS_PAID = 'paid';

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'purchase_order_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'balance',
        'status',           // draft, submitted, approved, rejected, cancelled
        'payment_status',    // unpaid, partially_paid, paid
        'payment_method',
        'reference_number',
        'notes',
        'terms',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'approved_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentVoucher::class, 'supplier_invoice_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_UNPAID);
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PARTIALLY_PAID);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('payment_status', [
                self::PAYMENT_STATUS_UNPAID,
                self::PAYMENT_STATUS_PARTIALLY_PAID
            ]);
    }

    // Status helpers
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isUnpaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_UNPAID;
    }

    public function isPartiallyPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIALLY_PAID;
    }
}