<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_vouchers';

    protected $fillable = [
        'voucher_number',
        'supplier_id',
        'supplier_invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'bank_name',
        'bank_account',
        'status',
        'description',
        'notes',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
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

    public function supplierInvoice()
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Methods
    public static function generateVoucherNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $last = self::where('voucher_number', 'like', "PV/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $parts = explode('/', $last->voucher_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "PV/{$year}/{$month}/{$sequence}";
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucwords($this->status);
    }

    public function getFormattedAmountAttribute()
    {
        return 'TZS ' . number_format($this->amount, 2);
    }

    public function getMethodTextAttribute()
    {
        return str_replace('_', ' ', ucwords($this->payment_method));
    }

    public function getBankDetailsAttribute()
    {
        if ($this->bank_name && $this->bank_account) {
            return $this->bank_name . ' - ' . $this->bank_account;
        }
        return null;
    }
}