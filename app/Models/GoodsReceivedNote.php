<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceivedNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'goods_received_notes';

    protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'supplier_id',
        'receipt_date',
        'delivery_note_number',
        'received_by',
        'notes',
        'status',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(GRNItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice()
    {
        return $this->hasOne(SupplierInvoice::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('receipt_date', [$from, $to]);
    }

    // Methods
    public static function generateGRNNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $last = self::where('grn_number', 'like', "GRN/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($last) {
            $parts = explode('/', $last->grn_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "GRN/{$year}/{$month}/{$sequence}";
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity_accepted');
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancellable()
    {
        return $this->status === 'completed';
    }
}