<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an action on a model
     */
    public static function log(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        $user = Auth::user();
        
        // Determine changed fields
        $changedFields = [];
        if ($oldValues && $newValues) {
            foreach ($newValues as $key => $value) {
                if (isset($oldValues[$key]) && $oldValues[$key] != $value) {
                    $changedFields[] = $key;
                } elseif (!isset($oldValues[$key]) && $value !== null) {
                    $changedFields[] = $key;
                }
            }
        }

        // Get model identifier (e.g., payment_number, invoice_number)
        $modelIdentifier = null;
        if (method_exists($model, 'getAuditIdentifier')) {
            $modelIdentifier = $model->getAuditIdentifier();
        } else {
            $possibleFields = ['payment_number', 'invoice_number', 'journal_number', 
                               'transaction_number', 'account_code', 'name', 'title'];
            foreach ($possibleFields as $field) {
                if (isset($model->$field)) {
                    $modelIdentifier = $model->$field;
                    break;
                }
            }
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->email ?? 'System',
            'user_email' => $user?->email,
            'user_role' => $user?->roles?->pluck('name')->join(', ') ?? 'N/A',
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'model_identifier' => $modelIdentifier,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'description' => $description ?? self::generateDescription($action, $model, $modelIdentifier),
            'metadata' => [
                'session_id' => session()->getId(),
                'request_id' => uniqid(),
            ],
        ]);
    }

    /**
     * Log a transaction
     */
    public static function logTransaction(
        string $referenceType,
        int $referenceId,
        string $transactionType,
        float $amount,
        string $status,
        ?array $beforeStatus = null,
        ?array $afterStatus = null,
        ?string $description = null
    ): TransactionLog {
        $user = Auth::user();
        
        // Generate unique transaction number
        $transactionNumber = 'TXN-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

        return TransactionLog::create([
            'transaction_number' => $transactionNumber,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_type' => $transactionType,
            'amount' => $amount,
            'currency' => 'TZS',
            'status' => $status,
            'before_status' => $beforeStatus,
            'after_status' => $afterStatus,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->email ?? 'System',
            'ip_address' => Request::ip(),
            'description' => $description,
            'metadata' => [
                'session_id' => session()->getId(),
                'user_agent' => Request::userAgent(),
            ],
            'transaction_date' => now(),
        ]);
    }

    /**
     * Generate description automatically
     */
    private static function generateDescription(string $action, Model $model, ?string $identifier): string
    {
        $modelName = class_basename($model);
        
        return match($action) {
            'created' => "{$modelName} created: {$identifier}",
            'updated' => "{$modelName} updated: {$identifier}",
            'deleted' => "{$modelName} deleted: {$identifier}",
            'restored' => "{$modelName} restored: {$identifier}",
            'viewed' => "{$modelName} viewed: {$identifier}",
            'exported' => "{$modelName} exported: {$identifier}",
            'posted' => "{$modelName} posted: {$identifier}",
            'locked' => "{$modelName} locked: {$identifier}",
            'unlocked' => "{$modelName} unlocked: {$identifier}",
            default => "{$action} on {$modelName}: {$identifier}",
        };
    }

    /**
     * Get audit trail for a specific model
     */
    public static function getModelAudits(Model $model, $limit = 50)
    {
        return AuditLog::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transaction logs for a specific reference
     */
    public static function getTransactionLogs(string $referenceType, int $referenceId, $limit = 50)
    {
        return TransactionLog::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}