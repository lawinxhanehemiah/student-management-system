<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'name',
        'tax_number',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'payment_terms',
        'credit_limit',
        'opening_balance',
        'current_balance',
        'bank_name',
        'bank_account',
        'bank_branch',
        'status',
        'notes',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceivedNotes()
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function invoices()
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function paymentVouchers()
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeWithBalance($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Methods
    public function updateBalance($amount, $type = 'increase')
    {
        if ($type === 'increase') {
            $this->current_balance += $amount;
        } else {
            $this->current_balance -= $amount;
        }
        $this->save();
    }

    public static function generateCode()
    {
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->supplier_code, 3)) + 1 : 1;
        return 'SUP' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getFormattedCreditLimitAttribute()
    {
        return 'TZS ' . number_format($this->credit_limit, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'TZS ' . number_format($this->current_balance, 2);
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->city, $this->country]);
        return implode(', ', $parts);
    }
}