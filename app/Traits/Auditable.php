<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait Auditable
{
    /**
     * Boot the auditable trait
     */
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            static::auditAfterCreate($model);
        });

        static::updated(function (Model $model) {
            static::auditAfterUpdate($model);
        });

        static::deleted(function (Model $model) {
            static::auditAfterDelete($model);
        });

        // Check if restored method exists before using it
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::auditAfterRestore($model);
            });
        }
    }

    /**
     * Audit after create
     */
    protected static function auditAfterCreate(Model $model)
    {
        static::createAuditLog('created', $model, null, $model->getAttributes());
    }

    /**
     * Audit after update
     */
    protected static function auditAfterUpdate(Model $model)
    {
        $oldValues = [];
        $newValues = [];
        
        foreach ($model->getDirty() as $key => $value) {
            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $value;
        }

        if (!empty($oldValues)) {
            static::createAuditLog('updated', $model, $oldValues, $newValues);
        }
    }

    /**
     * Audit after delete
     */
    protected static function auditAfterDelete(Model $model)
    {
        static::createAuditLog('deleted', $model, $model->getAttributes(), null);
    }

    /**
     * Audit after restore
     */
    protected static function auditAfterRestore(Model $model)
    {
        static::createAuditLog('restored', $model, null, $model->getAttributes());
    }

    /**
     * Create audit log directly without AuditService
     */
    protected static function createAuditLog(string $event, Model $model, $oldValues = null, $newValues = null)
    {
        try {
            // Skip if no user is logged in
            if (!Auth::check()) {
                return;
            }

            // Prepare audit data
            $auditData = [
                'user_id' => Auth::id(),
                'event' => $event,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert into audits table
            DB::table('audits')->insert($auditData);
            
        } catch (\Exception $e) {
            // Log error but don't break application
            Log::error('Audit failed: ' . $e->getMessage(), [
                'model' => get_class($model),
                'event' => $event,
                'id' => $model->getKey()
            ]);
        }
    }

    /**
     * Log custom action
     */
    public function auditCustom(string $action, ?string $description = null)
    {
        static::createAuditLog($action, $this, null, $this->getAttributes());
    }

    /**
     * Get audit trail for this model
     */
    public function audits()
    {
        // Return audits from database
        return DB::table('audits')
            ->where('auditable_type', get_class($this))
            ->where('auditable_id', $this->getKey())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get identifier for audit logs
     */
    public function getAuditIdentifier(): ?string
    {
        $possibleFields = ['payment_number', 'invoice_number', 'journal_number', 
                           'transaction_number', 'account_code', 'name', 'title'];
        
        foreach ($possibleFields as $field) {
            if (isset($this->$field) && !empty($this->$field)) {
                return $this->$field;
            }
        }
        
        return "ID: {$this->id}";
    }
}