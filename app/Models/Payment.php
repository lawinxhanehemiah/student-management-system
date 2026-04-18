<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;
use App\Traits\Auditable;

class Payment extends Model
{
    use SoftDeletes, Auditable;

    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIALLY_COMPLETED = 'partially_completed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PARTIALLY_COMPLETED,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_REFUNDED,
    ];

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CHEQUE = 'cheque';
    public const METHOD_MPESA = 'mpesa';
    public const METHOD_TIGO_PESA = 'tigo_pesa';
    public const METHOD_AIRTEL_MONEY = 'airtel_money';
    public const METHOD_HALOPESA = 'halopesa';
    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_DEBIT_CARD = 'debit_card';

    public const METHODS = [
        self::METHOD_CASH,
        self::METHOD_BANK_TRANSFER,
        self::METHOD_CHEQUE,
        self::METHOD_MPESA,
        self::METHOD_TIGO_PESA,
        self::METHOD_AIRTEL_MONEY,
        self::METHOD_HALOPESA,
        self::METHOD_CREDIT_CARD,
        self::METHOD_DEBIT_CARD,
    ];

    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    public const TXN_FULL_PAYMENT = 'full_payment';
    public const TXN_PARTIAL_PAYMENT = 'partial_payment';
    public const TXN_INSTALLMENT = 'installment';
    public const TXN_REFUND = 'refund';

    /*
    |--------------------------------------------------------------------------
    | Fillable Attributes
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'payment_number',
        'payable_type',
        'payable_id',
        'student_id',
        'academic_year_id',
        'payment_gateway_id',
        'amount',
        'paid_amount',
        'balance',
        'payment_method',
        'transaction_type',
        'control_number',
        'reference_number',
        'transaction_id',
        'receipt_number',
        'status',
        'status_history',
        'gateway_request',
        'gateway_response',
        'gateway_metadata',
        'attempts',
        'last_attempt_at',
        'paid_at',
        'notes',
        'metadata',
        'created_by',
        'updated_by'
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'status_history' => 'array',
        'gateway_request' => 'array',
        'gateway_response' => 'array',
        'gateway_metadata' => 'array',
        'metadata' => 'array',
        'attempts' => 'integer',
        'paid_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = static::generatePaymentNumber();
            }

            if ($payment->balance === null) {
                $payment->balance = $payment->amount;
            }

            if ($payment->paid_amount === null) {
                $payment->paid_amount = 0;
            }

            if (!$payment->status) {
                $payment->status = self::STATUS_PENDING;
            }

            if ($payment->attempts === null) {
                $payment->attempts = 0;
            }
        });

        static::updating(function (Payment $payment) {
            if ($payment->isDirty('amount') && $payment->exists) {
                throw new LogicException('Payment amount cannot be modified after creation.');
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Factory Methods
    |--------------------------------------------------------------------------
    */

    public static function generatePaymentNumber(): string
    {
        do {
            $number = 'PAY-' . now()->format('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (static::where('payment_number', $number)->exists());

        return $number;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function bankTransactions(): MorphMany
    {
        return $this->morphMany(BankTransaction::class, 'reference');
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'reference');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | State Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPartiallyCompleted(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isFullyPaid(): bool
    {
        return $this->balance == 0;
    }

    public function hasBalance(): bool
    {
        return $this->balance > 0;
    }

    public function getProgressPercentage(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->amount) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Guarded State Transitions
    |--------------------------------------------------------------------------
    */

    public function transitionTo(string $newStatus, ?string $notes = null): void
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new LogicException(sprintf(
                'Invalid status transition from %s to %s',
                $this->status,
                $newStatus
            ));
        }

        $history = $this->status_history ?? [];

        $history[] = [
            'from' => $this->status,
            'to' => $newStatus,
            'notes' => $notes,
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id()
        ];

        $this->update([
            'status' => $newStatus,
            'status_history' => $history
        ]);
    }

    protected function canTransitionTo(string $newStatus): bool
    {
        $map = [
            self::STATUS_PENDING => [
                self::STATUS_PARTIALLY_COMPLETED,
                self::STATUS_COMPLETED,
                self::STATUS_FAILED
            ],
            self::STATUS_PARTIALLY_COMPLETED => [
                self::STATUS_COMPLETED,
                self::STATUS_FAILED
            ],
            self::STATUS_FAILED => [
                self::STATUS_PENDING
            ],
            self::STATUS_COMPLETED => [
                self::STATUS_REFUNDED
            ],
            self::STATUS_REFUNDED => []
        ];

        return in_array($newStatus, $map[$this->status] ?? []);
    }

    /*
    |--------------------------------------------------------------------------
    | Pure State Mutations
    |--------------------------------------------------------------------------
    */

    public function applyFullPayment(string $transactionReference, array $metadata = []): void
    {
        if (!$this->isPending() && !$this->isPartiallyCompleted()) {
            throw new LogicException('Payment cannot be completed in current state.');
        }

        $this->update([
            'paid_amount' => $this->amount,
            'balance' => 0,
            'transaction_id' => $transactionReference,
            'receipt_number' => $transactionReference,
            'paid_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata)
        ]);

        $this->transitionTo(self::STATUS_COMPLETED);
    }

    public function applyPartialPayment(float $paidAmount, string $transactionReference, array $metadata = []): void
    {
        if ($paidAmount <= 0) {
            throw new InvalidArgumentException('Partial amount must be greater than zero.');
        }

        if ($paidAmount > $this->balance) {
            throw new InvalidArgumentException(
                "Partial amount ({$paidAmount}) exceeds remaining balance ({$this->balance})."
            );
        }

        if (!$this->isPending() && !$this->isPartiallyCompleted()) {
            throw new LogicException('Payment cannot accept partial payment in current state.');
        }

        $newPaidAmount = $this->paid_amount + $paidAmount;
        $newBalance = $this->amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'transaction_id' => $transactionReference,
            'receipt_number' => $transactionReference,
            'paid_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata)
        ]);

        $this->transitionTo(
            $newBalance == 0 ? self::STATUS_COMPLETED : self::STATUS_PARTIALLY_COMPLETED
        );
    }

    public function applyRefund(float $refundAmount, string $refundReference, array $metadata = []): void
    {
        if (!$this->isCompleted()) {
            throw new LogicException('Only completed payments can be refunded.');
        }

        if ($refundAmount <= 0) {
            throw new InvalidArgumentException('Refund amount must be greater than zero.');
        }

        if ($refundAmount > $this->paid_amount) {
            throw new InvalidArgumentException(
                "Refund amount ({$refundAmount}) exceeds paid amount ({$this->paid_amount})."
            );
        }

        $newPaidAmount = $this->paid_amount - $refundAmount;
        $newBalance = $this->amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'metadata' => array_merge($this->metadata ?? [], $metadata, [
                'refund_reference' => $refundReference,
                'refund_amount' => $refundAmount,
                'refunded_at' => now()->toIso8601String()
            ])
        ]);

        if ($newBalance > 0 && $newPaidAmount > 0) {
            $this->transitionTo(self::STATUS_PARTIALLY_COMPLETED);
        } elseif ($newPaidAmount == 0) {
            $this->transitionTo(self::STATUS_REFUNDED);
        }
    }

    public function markAsFailed(string $reason, array $metadata = []): void
    {
        if ($this->isCompleted()) {
            throw new LogicException('Completed payments cannot be marked as failed.');
        }

        $this->update([
            'metadata' => array_merge($this->metadata ?? [], $metadata, [
                'failure_reason' => $reason,
                'failed_at' => now()->toIso8601String()
            ])
        ]);

        $this->transitionTo(self::STATUS_FAILED, $reason);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePartiallyCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PARTIALLY_COMPLETED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeForPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    public function scopeWithBalance(Builder $query): Builder
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeWithoutBalance(Builder $query): Builder
    {
        return $query->where('balance', '<=', 0);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('created_at', now()->year);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_PENDING => 'badge bg-warning',
            self::STATUS_PARTIALLY_COMPLETED => 'badge bg-info',
            self::STATUS_COMPLETED => 'badge bg-success',
            self::STATUS_FAILED => 'badge bg-danger',
            self::STATUS_REFUNDED => 'badge bg-secondary'
        ];

        $class = $badges[$this->status] ?? 'badge bg-secondary';
        $label = str_replace('_', ' ', ucfirst($this->status));

        return "<span class=\"{$class}\">{$label}</span>";
    }

    public function getPaymentMethodNameAttribute(): string
    {
        $methods = [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHEQUE => 'Cheque',
            self::METHOD_MPESA => 'M-Pesa',
            self::METHOD_TIGO_PESA => 'Tigo Pesa',
            self::METHOD_AIRTEL_MONEY => 'Airtel Money',
            self::METHOD_HALOPESA => 'HaloPesa',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_DEBIT_CARD => 'Debit Card',
        ];

        return $methods[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Helpers
    |--------------------------------------------------------------------------
    */

    public function validateForPayment(): void
    {
        if ($this->amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        if (!$this->student_id) {
            throw new InvalidArgumentException('Payment must be associated with a student.');
        }

        if (!$this->academic_year_id) {
            throw new InvalidArgumentException('Payment must be associated with an academic year.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Methods
    |--------------------------------------------------------------------------
    */

    public function getAuditTrail(): array
    {
        return [
            'payment' => [
                'id' => $this->id,
                'number' => $this->payment_number,
                'amount' => $this->amount,
                'status' => $this->status
            ],
            'bank_transactions' => $this->bankTransactions()->count(),
            'journal_entries' => $this->journalEntries()->count(),
            'attempts' => $this->attempts()->count(),
            'refunds' => $this->refunds()->count(),
            'status_history' => $this->status_history ?? []
        ];
    }
}