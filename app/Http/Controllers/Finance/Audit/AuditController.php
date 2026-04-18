<?php

namespace App\Http\Controllers\Finance\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditController extends Controller
{
    /**
     * Allowed models for safe class resolution
     */
    protected $allowedModels = [
        'App\Models\Payment' => Payment::class,
        'App\Models\Invoice' => Invoice::class,
        'App\Models\JournalEntry' => JournalEntry::class,
        'App\Models\User' => User::class,
        'App\Models\BankTransaction' => 'App\Models\BankTransaction',
        'App\Models\ChartOfAccount' => 'App\Models\ChartOfAccount',
    ];

    /**
     * Default items per page
     */
    protected $defaultPerPage = 50;
    
    /**
     * Maximum allowed items per page
     */
    protected $maxPerPage = 200;

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected $cacheTTL = 3600;

    /**
     * Cache keys
     */
    const CACHE_KEY_STATS = 'audit_stats';
    const CACHE_KEY_FILTERS = 'audit_filters';
    const CACHE_KEY_TRANSACTION_STATS = 'transaction_stats';

    /**
     * Display audit trail
     */
    public function auditTrail(Request $request)
    {
        // Enforce per page limit
        $perPage = min(
            $request->get('per_page', $this->defaultPerPage),
            $this->maxPerPage
        );
        
        // Apply filters with efficient date range
        $query = $this->buildAuditQuery($request);
        $logs = $query->paginate($perPage);
        
        // Get filter options (cached)
        $filterOptions = $this->getAuditFilterOptions();

        // Get cached statistics
        $stats = $this->getAuditStatistics();

        return view('finance.audit.audit-trail', array_merge(
            compact('logs', 'stats'),
            $filterOptions
        ));
    }

    /**
     * Display transaction logs
     */
    public function transactionLogs(Request $request)
    {
        $perPage = min(
            $request->get('per_page', $this->defaultPerPage),
            $this->maxPerPage
        );
        
        $query = $this->buildTransactionQuery($request);
        $logs = $query->paginate($perPage);

        $stats = $this->getTransactionStatistics();
        
        $filterOptions = $this->getTransactionFilterOptions();

        return view('finance.audit.transaction-logs', array_merge(
            compact('logs', 'stats'),
            $filterOptions
        ));
    }

    /**
     * Display role activity logs
     */
    public function roleActivity(Request $request)
    {
        $perPage = min(
            $request->get('per_page', $this->defaultPerPage),
            $this->maxPerPage
        );
        
        $query = $this->buildRoleQuery($request);
        $logs = $query->paginate($perPage);

        // Get role summary with single optimized query
        $roleSummary = $this->getRoleSummary();
        
        // Get activity by role with single grouped query
        $activityByRole = $this->getActivityByRole();

        return view('finance.audit.role-activity', compact('logs', 'roleSummary', 'activityByRole'));
    }

    /**
     * Show audit log details
     */
    public function showAudit($id)
    {
        $log = AuditLog::findOrFail($id);
        
        // Safely load model with whitelist and soft delete check
        $model = $this->resolveModelSafely($log->model_type, $log->model_id);

        return view('finance.audit.show-audit', compact('log', 'model'));
    }

    /**
     * Show transaction log details
     */
    public function showTransaction($id)
    {
        $log = TransactionLog::findOrFail($id);
        
        // Safely load reference with whitelist
        $reference = $this->resolveModelSafely($log->reference_type, $log->reference_id);

        return view('finance.audit.show-transaction', compact('log', 'reference'));
    }

    /**
     * Export audit logs - MEMORY SAFE WITH FULL FILTERS
     */
    public function export(Request $request)
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Date/Time',
                'User',
                'Role',
                'Action',
                'Model',
                'Identifier',
                'Description',
                'IP Address',
            ]);

            // ✅ FIXED: Apply ALL filters including date range
            $query = $this->buildAuditQuery($request);
            
            // Use chunk() to avoid memory issues
            $query->chunk(1000, function($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user_name,
                        $log->user_role,
                        $log->action,
                        class_basename($log->model_type),
                        $log->model_identifier,
                        $log->description,
                        $log->ip_address,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========== PRIVATE QUERY BUILDERS ===========

    /**
     * Build audit query with all filters
     */
    private function buildAuditQuery(Request $request)
    {
        $query = AuditLog::query();

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Model type filter
        if ($request->filled('model_type')) {
            $query->where('model_type', 'LIKE', '%' . $request->model_type . '%');
        }

        // ✅ EFFICIENT DATE RANGE FILTERS
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // ✅ FULLTEXT SEARCH (if available)
        if ($request->filled('search')) {
            $search = $request->search;
            
            // Check if fulltext index exists
            if ($this->hasFulltextIndex('audit_logs')) {
                $query->whereRaw("MATCH(model_identifier, description, user_name) AGAINST(? IN BOOLEAN MODE)", [$search]);
            } else {
                // Fallback to LIKE (slower but works)
                $query->where(function($q) use ($search) {
                    $q->where('model_identifier', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('user_name', 'LIKE', "%{$search}%");
                });
            }
        }

        return $query->latest();
    }

    /**
     * Build transaction query with all filters
     */
    private function buildTransactionQuery(Request $request)
    {
        $query = TransactionLog::query();

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Transaction type filter
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        // Reference type filter
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        // ✅ EFFICIENT DATE RANGE FILTERS
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to . ' 23:59:59');
        }

        // Amount filters
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('user_name', 'LIKE', "%{$search}%");
            });
        }

        return $query->latest('transaction_date');
    }

    /**
     * Build role activity query
     */
    private function buildRoleQuery(Request $request)
    {
        $query = AuditLog::whereNotNull('user_role');

        if ($request->filled('role')) {
            $query->where('user_role', 'LIKE', "%{$request->role}%");
        }

        // ✅ EFFICIENT DATE RANGE FILTERS
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        return $query->latest();
    }

    // =========== CACHED DATA METHODS ===========

    /**
     * Get audit filter options (cached)
     */
    private function getAuditFilterOptions()
    {
        return Cache::remember(self::CACHE_KEY_FILTERS, $this->cacheTTL, function() {
            return [
                'users' => User::select('id', 'first_name', 'last_name')
                    ->orderBy('first_name')
                    ->orderBy('last_name')
                    ->get(),
                'actions' => AuditLog::distinct('action')->pluck('action'),
                'models' => AuditLog::distinct('model_type')->pluck('model_type'),
            ];
        });
    }

    /**
     * Get audit statistics (cached)
     */
    private function getAuditStatistics()
    {
        return Cache::remember(self::CACHE_KEY_STATS, $this->cacheTTL, function() {
            $today = now()->format('Y-m-d');
            $startOfWeek = now()->startOfWeek()->format('Y-m-d');
            $endOfWeek = now()->endOfWeek()->format('Y-m-d');
            
            // ✅ FIXED: Use range queries instead of DATE() function
            $stats = AuditLog::selectRaw('
                COUNT(*) as total_logs,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as week_count,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as month_count
            ', [
                $today . ' 00:00:00', $today . ' 23:59:59',
                $startOfWeek . ' 00:00:00', $endOfWeek . ' 23:59:59',
                now()->startOfMonth()->format('Y-m-d') . ' 00:00:00', 
                now()->endOfMonth()->format('Y-m-d') . ' 23:59:59'
            ])->first();

            $byAction = AuditLog::select('action', DB::raw('count(*) as total'))
                ->groupBy('action')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            return [
                'total_logs' => $stats->total_logs ?? 0,
                'today' => $stats->today_count ?? 0,
                'this_week' => $stats->week_count ?? 0,
                'this_month' => $stats->month_count ?? 0,
                'by_action' => $byAction,
            ];
        });
    }

    /**
     * Get transaction statistics (cached)
     */
    private function getTransactionStatistics()
    {
        return Cache::remember(self::CACHE_KEY_TRANSACTION_STATS, $this->cacheTTL, function() {
            $today = now()->format('Y-m-d');
            
            $byType = TransactionLog::select('transaction_type', 
                    DB::raw('count(*) as count'), 
                    DB::raw('sum(amount) as total'))
                ->groupBy('transaction_type')
                ->get();

            return [
                'total_transactions' => TransactionLog::count(),
                'total_amount' => TransactionLog::sum('amount'),
                'today_amount' => TransactionLog::where('transaction_date', '>=', $today . ' 00:00:00')
                    ->where('transaction_date', '<=', $today . ' 23:59:59')
                    ->sum('amount'),
                'by_type' => $byType,
            ];
        });
    }

    /**
     * Get transaction filter options
     */
    private function getTransactionFilterOptions()
    {
        return [
            'users' => User::select('id', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'transactionTypes' => TransactionLog::distinct('transaction_type')->pluck('transaction_type'),
            'referenceTypes' => TransactionLog::distinct('reference_type')->pluck('reference_type'),
        ];
    }

    /**
     * Get role summary
     */
    private function getRoleSummary()
    {
        return AuditLog::select('user_role', DB::raw('count(*) as total'))
            ->whereNotNull('user_role')
            ->groupBy('user_role')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get activity by role (single query)
     */
    private function getActivityByRole()
    {
        $results = AuditLog::select('user_role', 'action', DB::raw('count(*) as count'))
            ->whereNotNull('user_role')
            ->groupBy('user_role', 'action')
            ->orderBy('user_role')
            ->orderBy('count', 'desc')
            ->get();

        $activityByRole = [];
        
        foreach ($results as $row) {
            if (!isset($activityByRole[$row->user_role])) {
                $activityByRole[$row->user_role] = [
                    'total' => 0,
                    'actions' => []
                ];
            }
            
            $activityByRole[$row->user_role]['actions'][] = (object)[
                'action' => $row->action,
                'count' => $row->count
            ];
            $activityByRole[$row->user_role]['total'] += $row->count;
        }

        // Limit to top 5
        foreach ($activityByRole as $role => &$data) {
            usort($data['actions'], fn($a, $b) => $b->count <=> $a->count);
            $data['actions'] = array_slice($data['actions'], 0, 5);
        }

        return $activityByRole;
    }

    // =========== UTILITY METHODS ===========

    /**
     * Safely resolve model with whitelist and soft delete check
     */
    private function resolveModelSafely($modelType, $modelId)
    {
        if (!$modelType || !$modelId) {
            return null;
        }

        // Check whitelist
        if (!isset($this->allowedModels[$modelType])) {
            return null;
        }

        try {
            $modelClass = $this->allowedModels[$modelType];
            
            if (in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
                return $modelClass::withTrashed()->find($modelId);
            }
            
            return $modelClass::find($modelId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if table has fulltext index
     */
    private function hasFulltextIndex($table)
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Index_type = 'FULLTEXT'");
        return !empty($result);
    }

    /**
     * Clear all cache (called from model events)
     */
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY_STATS);
        Cache::forget(self::CACHE_KEY_FILTERS);
        Cache::forget(self::CACHE_KEY_TRANSACTION_STATS);
    }
}