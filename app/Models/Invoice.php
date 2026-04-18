<?php
// app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Auditable;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'invoice_number',
        'control_number',              
        'control_number_status',       
        'control_number_expiry',       
        'payment_api_response',        
        'payment_request_id',          
        'student_id',
        'user_id',
        'academic_year_id',
        'programme_fee_id',
        'invoice_type',
        'total_amount',
        'paid_amount',
        'balance',
        'issue_date',
        'due_date',
        'status',
        'payment_status',
        'description',
        'notes',
        'metadata',
        'payment_method',
        'payment_reference',
        'paid_at',
        'created_by'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'control_number_expiry' => 'datetime'
    ];

    /**
     * Get the student that owns the invoice
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user that owns the invoice
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the academic year
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the programme fee
     */
    public function programmeFee()
    {
        return $this->belongsTo(ProgrammeFee::class);
    }

    /**
     * Get the invoice items
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the transactions for this invoice
     */
    public function transactions()
    {
        return $this->hasMany(FeeTransaction::class, 'reference_id', 'id')
            ->where('reference_type', 'App\\Models\\Invoice');
    }

    /**
     * Get the payments for the invoice (MorphMany relationship)
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get the notifications for the invoice
     */
    public function notifications()
    {
        return $this->hasMany(PaymentNotification::class);
    }

    /**
     * Get the user who created the invoice
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get invoice type name
     */
    public function getTypeNameAttribute()
    {
        $types = [
            'tuition' => 'Tuition Fee',
            'registration' => 'Registration Fee',
            'repeat_module' => 'Repeat Module Fee',
            'supplementary' => 'Supplementary Fee',
            'hostel' => 'Hostel Fee',
            'other' => 'Other Fee'
        ];
        
        return $types[$this->invoice_type] ?? ucfirst(str_replace('_', ' ', $this->invoice_type));
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return 'TZS ' . number_format($this->total_amount, 0);
    }

    /**
     * Get formatted paid amount
     */
    public function getFormattedPaidAttribute()
    {
        return 'TZS ' . number_format($this->paid_amount, 0);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return 'TZS ' . number_format($this->balance, 0);
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgressAttribute()
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->due_date < now() && 
               $this->payment_status != 'paid' && 
               $this->balance > 0;
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid()
    {
        return $this->payment_status == 'paid' || $this->balance <= 0;
    }

    /**
     * Check if invoice is partially paid
     */
    public function isPartial()
    {
        return $this->payment_status == 'partial' || 
               ($this->paid_amount > 0 && $this->paid_amount < $this->total_amount);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        if ($this->isOverdue()) {
            return 'danger';
        }
        
        $classes = [
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'secondary',
            'unpaid' => 'danger'
        ];
        
        return $classes[$this->payment_status] ?? 'secondary';
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus()
    {
        if ($this->balance <= 0) {
            $this->payment_status = 'paid';
            $this->status = 'paid';
            if (!$this->paid_at) {
                $this->paid_at = now();
            }
        } elseif ($this->paid_amount > 0 && $this->paid_amount < $this->total_amount) {
            $this->payment_status = 'partial';
            $this->status = 'pending';
        } else {
            $this->payment_status = 'unpaid';
            $this->status = 'pending';
        }
        
        $this->saveQuietly();
        
        return $this;
    }

    /**
     * Add payment to invoice - COMPLETE FIXED VERSION
     * 
     * @param float $amount
     * @param string $paymentMethod
     * @param string|null $reference
     * @param string|null $notes
     * @param array $metadata
     * @return $this
     * @throws \Exception
     */
    public function addPayment($amount, $paymentMethod, $reference = null, $notes = null, $metadata = [])
    {
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero');
        }
        
        if ($amount > $this->balance) {
            throw new \Exception('Payment amount exceeds invoice balance of ' . number_format($this->balance, 2));
        }
        
        DB::beginTransaction();
        
        try {
            // Calculate new values
            $newPaid = $this->paid_amount + $amount;
            $newBalance = $this->total_amount - $newPaid;
            
            // Update invoice
            $this->paid_amount = $newPaid;
            $this->balance = $newBalance;
            $this->payment_method = $paymentMethod;
            $this->payment_reference = $reference;
            
            // Update payment status
            if ($newBalance <= 0) {
                $this->payment_status = 'paid';
                $this->status = 'paid';
                $this->paid_at = now();
            } elseif ($newPaid > 0 && $newPaid < $this->total_amount) {
                $this->payment_status = 'partial';
                $this->status = 'pending';
            }
            
            // SAFELY handle metadata (could be string, array, or null)
            $currentMetadata = [];
            
            if (is_array($this->metadata)) {
                $currentMetadata = $this->metadata;
            } elseif (is_string($this->metadata) && !empty($this->metadata)) {
                $decoded = json_decode($this->metadata, true);
                $currentMetadata = is_array($decoded) ? $decoded : [];
            } elseif (is_null($this->metadata)) {
                $currentMetadata = [];
            }
            
            // Add payment info to metadata
            $currentMetadata['last_payment'] = [
                'amount' => $amount,
                'method' => $paymentMethod,
                'reference' => $reference,
                'notes' => $notes,
                'metadata' => $metadata,
                'timestamp' => now()->toIso8601String()
            ];
            
            if (!empty($metadata)) {
                if (!isset($currentMetadata['payment_details'])) {
                    $currentMetadata['payment_details'] = [];
                }
                $currentMetadata['payment_details'][] = $metadata;
            }
            
            $this->metadata = $currentMetadata;
            
            // Save invoice
            $this->save();
            
            // ========== CREATE PAYMENT RECORD ==========
            if (class_exists('App\Models\Payment')) {
                try {
                    // Check if we're in a console/queue environment (no auth user)
                    $createdBy = null;
                    if (function_exists('auth') && auth()->check()) {
                        $createdBy = auth()->id();
                    }
                    
                    $paymentData = [
                        'payment_number' => Payment::generatePaymentNumber(),
                        'payable_type' => self::class,
                        'payable_id' => $this->id,
                        'student_id' => $this->student_id,
                        'academic_year_id' => $this->academic_year_id,
                        'payment_gateway_id' => null,
                        'amount' => $amount,
                        'paid_amount' => $amount,
                        'balance' => 0,
                        'payment_method' => $paymentMethod,
                        'transaction_type' => $amount < $this->total_amount ? 'partial_payment' : 'full_payment',
                        'control_number' => $this->control_number,
                        'reference_number' => $reference,
                        'transaction_id' => $reference,
                        'receipt_number' => $reference ?? ('RCT-' . time()),
                        'status' => 'completed',
                        'paid_at' => now(),
                        'notes' => $notes,
                        'metadata' => $metadata,
                        'created_by' => $createdBy
                    ];
                    
                    // Remove any null values
                    $paymentData = array_filter($paymentData, function($value) {
                        return !is_null($value);
                    });
                    
                    $payment = Payment::create($paymentData);
                    
                    Log::info('Payment record created successfully', [
                        'payment_id' => $payment->id, 
                        'invoice_id' => $this->id,
                        'payment_number' => $payment->payment_number
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Failed to create payment record', [
                        'invoice_id' => $this->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't throw - we still want to commit the invoice update
                }
            }
            
            // ========== CREATE FEE TRANSACTION ==========
            if (class_exists('App\Models\FeeTransaction')) {
                try {
                    FeeTransaction::create([
                        'student_id' => $this->student_id,
                        'academic_year_id' => $this->academic_year_id,
                        'control_number' => $this->control_number,
                        'receipt_number' => $reference ?? ('RCT-' . time()),
                        'transaction_type' => 'PAYMENT',
                        'description' => $notes ?? "Payment for invoice {$this->invoice_number}",
                        'debit' => 0,
                        'credit' => $amount,
                        'running_balance' => $newBalance,
                        'reference_id' => $this->id,
                        'reference_type' => self::class,
                        'transaction_date' => now(),
                        'metadata' => json_encode(array_merge([
                            'payment_method' => $paymentMethod,
                            'reference' => $reference,
                            'notes' => $notes
                        ], $metadata))
                    ]);
                    
                    Log::info('Fee transaction created successfully', ['invoice_id' => $this->id]);
                    
                } catch (\Exception $e) {
                    Log::warning('Failed to create fee transaction', [
                        'invoice_id' => $this->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Payment added to invoice successfully', [
                'invoice_id' => $this->id,
                'amount' => $amount,
                'new_balance' => $newBalance,
                'payment_status' => $this->payment_status
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add payment to invoice', [
                'invoice_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
        
        return $this;
    }

    /**
     * Check if control number is expired
     */
    public function isControlNumberExpired()
    {
        if (!$this->control_number_expiry) {
            return false;
        }
        
        return now()->greaterThan($this->control_number_expiry);
    }

    /**
     * Check if control number is valid
     */
    public function isControlNumberValid()
    {
        return $this->control_number && 
               $this->control_number_status === 'generated' && 
               !$this->isControlNumberExpired();
    }

    /**
     * Extend control number expiry
     */
    public function extendControlNumber($days = 30)
    {
        $this->control_number_expiry = now()->addDays($days);
        $this->control_number_status = 'extended';
        $this->save();
        
        return $this;
    }

    // SCOPES
    
    /**
     * Scope pending invoices
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0);
    }

    /**
     * Scope unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope partial paid invoices
     */
    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    /**
     * Scope by academic year
     */
    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope by invoice type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    /**
     * Scope by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    /**
     * Scope with valid control number
     */
    public function scopeWithValidControlNumber($query)
    {
        return $query->whereNotNull('control_number')
            ->where('control_number_status', 'generated')
            ->where(function($q) {
                $q->whereNull('control_number_expiry')
                  ->orWhere('control_number_expiry', '>', now());
            });
    }

    /**
     * Get invoice statistics
     */
    public static function getStatistics($academicYearId = null)
    {
        $query = self::query();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        return [
            'total' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'total_paid' => $query->sum('paid_amount'),
            'total_balance' => $query->sum('balance'),
            'paid_count' => $query->where('payment_status', 'paid')->count(),
            'unpaid_count' => $query->where('payment_status', 'unpaid')->count(),
            'partial_count' => $query->where('payment_status', 'partial')->count(),
            'overdue_count' => $query->overdue()->count()
        ];
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $count = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;
        
        $sequence = str_pad($count, 5, '0', STR_PAD_LEFT);
        
        return "INV/{$year}/{$month}/{$sequence}";
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            
            if (empty($invoice->control_number_status)) {
                $invoice->control_number_status = 'pending';
            }
            
            if (empty($invoice->issue_date)) {
                $invoice->issue_date = now();
            }
            
            if (empty($invoice->status)) {
                $invoice->status = 'pending';
            }
            
            if (empty($invoice->payment_status)) {
                $invoice->payment_status = 'unpaid';
            }
        });
        
        static::updating(function ($invoice) {
            // Auto-update payment status if balance changes
            if ($invoice->isDirty('paid_amount') || $invoice->isDirty('balance')) {
                $invoice->updatePaymentStatus();
            }
        });
    }

    public function creditNotes()
{
    return $this->hasMany(CreditNote::class);
}

public function refunds()
{
    return $this->hasMany(Refund::class);
}

public function creditNoteApplications()
{
    return $this->hasManyThrough(CreditNoteApplication::class, CreditNote::class);
}

}