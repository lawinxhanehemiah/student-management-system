<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AcademicYear;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\SupplierInvoice;
use App\Models\DepartmentBudget;
use App\Models\Asset;
use App\Models\Requisition;
use App\Models\Tender;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Display the finance dashboard
     */
    public function dashboard(Request $request)
{
    $period = $request->get('period', 'month');
    $selectedYearId = $request->get('academic_year');
    
    // Get all academic years for filter dropdown
    $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
    
    // Get current academic year
    $currentAcademicYear = AcademicYear::where('is_active', true)->first();
    
    // Get current fiscal year
    $currentFiscalYear = FiscalYear::where('is_active', true)->first();
    
    // If no year selected, use current academic year or first available
    if (!$selectedYearId) {
        $selectedYearId = $currentAcademicYear ? $currentAcademicYear->id : ($academicYears->first()?->id);
    }
    
    // Get selected academic year object
    $selectedAcademicYear = $selectedYearId ? AcademicYear::find($selectedYearId) : null;
    
    // Get stats for selected year
    $stats = $this->getDashboardStats($selectedYearId);
    
    // Get financial health metrics (always overall, not filtered by year)
    $financialHealth = $this->getFinancialHealthMetrics();
    
    // Get chart data (filtered by selected year)
    $monthlyData = $this->getMonthlyCollectionData($selectedYearId);
    $weeklyData = $this->getWeeklyCollectionData($selectedYearId);
    
    // Get recent data (filtered by selected year)
    $recentInvoices = Invoice::with(['student.user'])
        ->when($selectedYearId, fn($q) => $q->where('academic_year_id', $selectedYearId))
        ->latest()
        ->limit(10)
        ->get();
    
    $recentPayments = Payment::with(['student.user'])
        ->where('status', 'completed')
        ->when($selectedYearId, function($q) use ($selectedYearId) {
            return $q->whereHas('payable', function($query) use ($selectedYearId) {
                $query->where('academic_year_id', $selectedYearId);
            });
        })
        ->latest()
        ->limit(10)
        ->get();
    
    // Fee distribution (filtered by selected year)
    $feeDistribution = $this->getFeeTypeDistribution($selectedYearId);
    
    // Outstanding summary (filtered by selected year)
    $outstandingSummary = $this->getOutstandingSummary($selectedYearId);
    
    // Payables (always overall - supplier invoices not tied to academic year)
    $payablesSummary = $this->getPayablesSummary();
    $recentPayables = SupplierInvoice::with('supplier')
        ->where('payment_status', '!=', 'paid')
        ->latest()
        ->limit(5)
        ->get();
    
    // Bank & Cash (always overall)
    $bankAccounts = BankAccount::where('is_active', true)->get();
    $totalCashPosition = $bankAccounts->sum('current_balance');
    $cashFlowSummary = $this->getCashFlowSummary();
    $cashFlowChart = $this->getCashFlowChartData();
    
    // Budget (filtered by fiscal year, not academic year)
    $budgetSummary = $this->getBudgetSummary();
    $budgetChart = $this->getBudgetChartData();
    
    // Assets (always overall)
    $assetSummary = $this->getAssetSummary();
    
    // Procurement (always overall)
    $procurementSummary = $this->getProcurementSummary();
    
    // Fee breakdowns (filtered by selected year)
    $breakdowns = $this->getFeeBreakdowns($selectedYearId);

    return view('finance.dashboard', array_merge(
        compact(
            'stats',
            'financialHealth',
            'monthlyData',
            'weeklyData',
            'recentInvoices',
            'recentPayments',
            'feeDistribution',
            'outstandingSummary',
            'payablesSummary',
            'recentPayables',
            'bankAccounts',
            'totalCashPosition',
            'cashFlowSummary',
            'cashFlowChart',
            'budgetSummary',
            'budgetChart',
            'assetSummary',
            'procurementSummary',
            'currentAcademicYear',
            'currentFiscalYear',
            'academicYears',
            'selectedYearId',
            'selectedAcademicYear',
            'period'
        ),
        $breakdowns
    ));
}


    /**
     * ADD THIS METHOD - Get chart data
     */
    private function getChartData($academicYearId = null)
    {
        return [
            'monthly' => $this->getMonthlyCollectionData($academicYearId),
            'weekly' => $this->getWeeklyCollectionData($academicYearId),
        ];
    }

    /**
     * ADD THIS METHOD - Get recent data
     */
    private function getRecentData($academicYearId = null)
    {
        $invoicesQuery = Invoice::with(['student.user']);
        $paymentsQuery = Payment::with(['student.user'])->where('status', 'completed');
        
        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
            $paymentsQuery->whereHas('payable', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }
        
        return [
            'invoices' => $invoicesQuery->latest()->limit(10)->get(),
            'payments' => $paymentsQuery->latest()->limit(10)->get(),
        ];
    }
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($academicYearId = null)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $query = Invoice::query();
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $totalAmount = $query->sum('total_amount');
        $totalPaid = $query->sum('paid_amount');
        $totalBalance = $query->sum('balance');

        return [
            'total_invoices' => $query->count(),
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            
            'today_collections' => Payment::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount'),
            
            'week_collections' => Payment::whereBetween('created_at', [$startOfWeek, Carbon::now()])
                ->where('status', 'completed')
                ->sum('amount'),
            
            'month_collections' => Payment::whereBetween('created_at', [$startOfMonth, Carbon::now()])
                ->where('status', 'completed')
                ->sum('amount'),
            
            'pending_invoices' => Invoice::where('balance', '>', 0)
                ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                ->count(),
            
            'overdue_invoices' => Invoice::where('due_date', '<', $today)
                ->where('balance', '>', 0)
                ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                ->count(),
            
            'paid_percentage' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0,
            'collection_rate' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0,
        ];
    }

    /**
     * Get financial health metrics
     */
    private function getFinancialHealthMetrics()
    {
        $currentAssets = ChartOfAccount::where('account_type', 'asset')
            ->where('category', 'current_asset')
            ->sum('current_balance');
        
        $currentLiabilities = ChartOfAccount::where('account_type', 'liability')
            ->where('category', 'current_liability')
            ->sum('current_balance');
        
        $currentRatio = $currentLiabilities > 0 ? $currentAssets / $currentLiabilities : 0;

        $totalBudget = DepartmentBudget::sum('allocated_amount');
        $totalExpenses = ChartOfAccount::where('account_type', 'expense')->sum('current_balance');
        $budgetUtilization = $totalBudget > 0 ? round(($totalExpenses / $totalBudget) * 100, 1) : 0;

        $avgDailyRevenue = Payment::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(90))
            ->avg('amount') ?: 1;
        
        $accountsReceivable = ChartOfAccount::where('account_code', 'LIKE', '1-02-%')
            ->sum('current_balance');
        
        $dso = $avgDailyRevenue > 0 ? round($accountsReceivable / $avgDailyRevenue) : 0;

        return [
            'current_ratio' => round($currentRatio, 2),
            'current_assets' => $currentAssets,
            'current_liabilities' => $currentLiabilities,
            'budget_utilization' => $budgetUtilization,
            'budget_used' => $totalExpenses,
            'dso' => $dso,
        ];
    }

    /**
     * Get monthly collection data
     */
    private function getMonthlyCollectionData($academicYearId = null)
    {
        $months = [];
        $collections = [];
        $targets = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $paymentQuery = Payment::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->where('status', 'completed');
                
            if ($academicYearId) {
                $paymentQuery->whereHas('payable', function($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            }
            
            $collected = $paymentQuery->sum('amount');
            $collections[] = $collected;
            
            $invoiceQuery = Invoice::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year);
                
            if ($academicYearId) {
                $invoiceQuery->where('academic_year_id', $academicYearId);
            }
            
            $expected = $invoiceQuery->sum('total_amount');
            $targets[] = $expected * 0.9;
        }

        return [
            'labels' => $months,
            'collections' => $collections,
            'targets' => $targets
        ];
    }

    /**
     * Get weekly collection data
     */
   private function getWeeklyCollectionData($academicYearId = null)
    {
        $days = [];
        $amounts = [];
        $startOfWeek = Carbon::now()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $days[] = $day->format('D');
            
            $paymentQuery = Payment::whereDate('created_at', $day)
                ->where('status', 'completed');
                
            if ($academicYearId) {
                $paymentQuery->whereHas('payable', function($q) use ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                });
            }
            
            $amount = $paymentQuery->sum('amount');
            $amounts[] = $amount;
        }

        return [
            'labels' => $days,
            'amounts' => $amounts
        ];
    }
    /**
     * Get fee type distribution
     */
    private function getFeeTypeDistribution($academicYearId = null)
    {
        $distribution = [];
        
        $query = Invoice::select('invoice_type', DB::raw('SUM(total_amount) as total'));
        
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        
        $types = $query->groupBy('invoice_type')
            ->get();
        
        foreach ($types as $item) {
            $label = ucwords(str_replace('_', ' ', $item->invoice_type));
            $distribution[$label] = $item->total;
        }
        
        if (empty($distribution)) {
            $distribution = [
                'Tuition' => 0,
                'Repeat Module' => 0,
                'Supplementary' => 0,
                'Hostel' => 0
            ];
        }
        
        return $distribution;
    }

    /**
     * Get outstanding summary
     */
     private function getOutstandingSummary($academicYearId = null)
    {
        $today = Carbon::today();

        $baseQuery = Invoice::where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0);
            
        if ($academicYearId) {
            $baseQuery->where('academic_year_id', $academicYearId);
        }

        return [
            '0_30_days' => (clone $baseQuery)
                ->where('due_date', '>=', $today)
                ->where('due_date', '<=', $today->copy()->addDays(30))
                ->sum('balance'),
            
            '31_60_days' => (clone $baseQuery)
                ->where('due_date', '>=', $today->copy()->subDays(60))
                ->where('due_date', '<', $today->copy()->subDays(30))
                ->sum('balance'),
            
            '61_90_days' => (clone $baseQuery)
                ->where('due_date', '>=', $today->copy()->subDays(90))
                ->where('due_date', '<', $today->copy()->subDays(60))
                ->sum('balance'),
            
            '90_plus_days' => (clone $baseQuery)
                ->where('due_date', '<', $today->copy()->subDays(90))
                ->sum('balance'),
        ];
    }
    /**
     * Get payables summary
     */
    private function getPayablesSummary()
    {
        $today = Carbon::today();

        $total = SupplierInvoice::where('payment_status', '!=', 'paid')
            ->sum('total_amount');
        
        $dueSoon = SupplierInvoice::where('due_date', '<=', $today->copy()->addDays(30))
            ->where('due_date', '>=', $today)
            ->where('payment_status', '!=', 'paid')
            ->sum('total_amount');
        
        $overdue = SupplierInvoice::where('due_date', '<', $today)
            ->where('payment_status', '!=', 'paid')
            ->sum('total_amount');

        return [
            'total' => $total,
            'due_soon' => $dueSoon,
            'overdue' => $overdue,
        ];
    }

    /**
     * Get cash flow summary
     */
    private function getCashFlowSummary()
    {
        $startOfPeriod = now()->startOfMonth();
        
        $opening = BankTransaction::where('transaction_date', '<', $startOfPeriod)
            ->orderBy('id', 'desc')
            ->first()?->balance_after ?? 0;
        
        $inflows = BankTransaction::where('transaction_date', '>=', $startOfPeriod)
            ->whereIn('transaction_type', ['deposit', 'opening_balance'])
            ->sum('amount');
        
        $outflows = BankTransaction::where('transaction_date', '>=', $startOfPeriod)
            ->whereIn('transaction_type', ['withdrawal', 'transfer'])
            ->sum('amount');
        
        $closing = BankAccount::sum('current_balance');

        return [
            'opening' => $opening,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $inflows - $outflows,
            'closing' => $closing,
        ];
    }

    /**
     * Get cash flow chart data
     */
    private function getCashFlowChartData()
    {
        $labels = [];
        $balances = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $labels[] = $date->format('M Y');
            
            $balance = BankAccount::sum('current_balance');
            $balances[] = $balance;
        }

        return [
            'labels' => $labels,
            'balances' => $balances,
        ];
    }

    /**
     * Get budget summary
     */
    private function getBudgetSummary()
    {
        $budgetItems = DepartmentBudget::with('department')
            ->select('department_id', DB::raw('SUM(allocated_amount) as total_budget'))
            ->groupBy('department_id')
            ->get();

        $summary = [];
        foreach ($budgetItems as $item) {
            $actual = ChartOfAccount::where('account_type', 'expense')
                ->where('department_id', $item->department_id)
                ->sum('current_balance');
            
            $variance = $actual - $item->total_budget;
            $utilization = $item->total_budget > 0 ? round(($actual / $item->total_budget) * 100, 1) : 0;

            $summary[] = [
                'department' => $item->department->name ?? 'Unknown',
                'category' => $item->department->name ?? 'Unknown',
                'budget' => $item->total_budget,
                'actual' => $actual,
                'variance' => $variance,
                'utilization' => $utilization,
            ];
        }

        return $summary;
    }

    /**
     * Get budget chart data
     */
    private function getBudgetChartData()
    {
        $summary = $this->getBudgetSummary();
        
        return [
            'labels' => collect($summary)->pluck('category')->take(5)->values(),
            'budget' => collect($summary)->pluck('budget')->take(5)->values(),
            'actual' => collect($summary)->pluck('actual')->take(5)->values(),
        ];
    }

    /**
     * Get asset summary
     */
    private function getAssetSummary()
    {
        $totalAssets = Asset::count();
        $totalValue = Asset::sum('current_value');
        $totalDepreciation = Asset::sum(DB::raw('purchase_cost - current_value'));

        $byCategory = Asset::select('category_id', DB::raw('count(*) as count'), DB::raw('sum(current_value) as value'))
            ->with('category')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'count' => $item->count,
                    'value' => $item->value,
                ];
            });

        return [
            'total_assets' => $totalAssets,
            'total_value' => $totalValue,
            'total_depreciation' => $totalDepreciation,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get procurement summary
     */
    private function getProcurementSummary()
    {
        $pendingRequisitions = Requisition::whereIn('status', ['submitted', 'under_review'])->count();
        $requisitionValue = Requisition::whereIn('status', ['submitted', 'under_review'])->sum('estimated_total');
        
        $activeTenders = Tender::where('status', 'published')->count();
        $tendersClosingSoon = Tender::where('status', 'published')
            ->where('closing_date', '<=', now()->addDays(7))
            ->count();
        
        $activeContracts = Contract::where('status', 'active')->count();
        $contractValue = Contract::where('status', 'active')->sum('contract_value');
        
        $expiringContracts = Contract::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30))
            ->count();

        return [
            'pending_requisitions' => $pendingRequisitions,
            'requisition_value' => $requisitionValue,
            'active_tenders' => $activeTenders,
            'tenders_closing_soon' => $tendersClosingSoon,
            'active_contracts' => $activeContracts,
            'contract_value' => $contractValue,
            'expiring_contracts' => $expiringContracts,
        ];
    }

    /**
 * Export dashboard report
 */
public function exportReport(Request $request)
{
    $type = $request->get('type', 'executive');
    $format = $request->get('format', 'csv');
    $period = $request->get('period', 'current');
    $academicYearId = $request->get('academic_year');
    
    // Get data based on report type
    $data = $this->getReportData($type, $period, $academicYearId);
    
    // Generate filename
    $filename = $type . '-report-' . now()->format('Y-m-d') . '.' . $format;
    
    if ($format === 'csv') {
        return $this->exportToCsv($data, $type, $filename);
    } elseif ($format === 'excel') {
        return $this->exportToExcel($data, $type, $filename);
    } else {
        return $this->exportToPdf($data, $type, $filename);
    }
}

/**
 * Get report data based on type
 */
private function getReportData($type, $period, $academicYearId = null)
{
    switch ($type) {
        case 'executive':
            return [
                'stats' => $this->getDashboardStats($academicYearId),
                'financial_health' => $this->getFinancialHealthMetrics(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'period' => $period,
            ];
            
        case 'financial':
            return [
                'invoices' => Invoice::with('student.user')
                    ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                    ->latest()
                    ->limit(500)
                    ->get(),
                'payments' => Payment::with('student.user')
                    ->where('status', 'completed')
                    ->when($academicYearId, fn($q) => $q->whereHas('payable', fn($q) => $q->where('academic_year_id', $academicYearId)))
                    ->latest()
                    ->limit(500)
                    ->get(),
                'summary' => [
                    'total_invoiced' => Invoice::when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))->sum('total_amount'),
                    'total_paid' => Payment::where('status', 'completed')
                        ->when($academicYearId, fn($q) => $q->whereHas('payable', fn($q) => $q->where('academic_year_id', $academicYearId)))
                        ->sum('amount'),
                    'total_balance' => Invoice::when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))->sum('balance'),
                ]
            ];
            
        case 'receivables':
            return [
                'aging' => $this->getOutstandingSummary($academicYearId),
                'overdue' => Invoice::where('due_date', '<', now())
                    ->where('balance', '>', 0)
                    ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
                    ->with('student.user')
                    ->get()
            ];
            
        case 'payables':
            return [
                'summary' => $this->getPayablesSummary(),
                'invoices' => SupplierInvoice::with('supplier')
                    ->where('payment_status', '!=', 'paid')
                    ->latest()
                    ->get()
            ];
            
        case 'budget':
            return [
                'summary' => $this->getBudgetSummary(),
                'chart' => $this->getBudgetChartData()
            ];
            
        default:
            return [];
    }
}

/**
 * Export to CSV
 */
private function exportToCsv($data, $type, $filename)
{
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($data, $type) {
        $file = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($file, [strtoupper(str_replace('_', ' ', $type)) . ' REPORT']);
        fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
        fputcsv($file, []);
        
        if ($type === 'executive') {
            fputcsv($file, ['EXECUTIVE SUMMARY']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Invoiced', number_format($data['stats']['total_amount'], 2)]);
            fputcsv($file, ['Total Collected', number_format($data['stats']['total_paid'], 2)]);
            fputcsv($file, ['Outstanding', number_format($data['stats']['total_balance'], 2)]);
            fputcsv($file, ['Collection Rate', $data['stats']['collection_rate'] . '%']);
            fputcsv($file, ['Pending Invoices', $data['stats']['pending_invoices']]);
            fputcsv($file, ['Overdue Invoices', $data['stats']['overdue_invoices']]);
            
        } elseif ($type === 'financial') {
            // Summary
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Invoiced', 'Total Paid', 'Total Balance']);
            fputcsv($file, [
                number_format($data['summary']['total_invoiced'], 2),
                number_format($data['summary']['total_paid'], 2),
                number_format($data['summary']['total_balance'], 2)
            ]);
            fputcsv($file, []);
            
            // Invoices
            fputcsv($file, ['INVOICES']);
            fputcsv($file, ['Invoice #', 'Student', 'Date', 'Amount', 'Paid', 'Balance', 'Status']);
            foreach ($data['invoices'] as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    ($invoice->student->user->first_name ?? '') . ' ' . ($invoice->student->user->last_name ?? ''),
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->total_amount,
                    $invoice->paid_amount,
                    $invoice->balance,
                    $invoice->payment_status
                ]);
            }
            
            // Payments
            fputcsv($file, []);
            fputcsv($file, ['PAYMENTS']);
            fputcsv($file, ['Payment #', 'Student', 'Method', 'Amount', 'Date', 'Status']);
            foreach ($data['payments'] as $payment) {
                fputcsv($file, [
                    $payment->payment_number,
                    ($payment->student->user->first_name ?? '') . ' ' . ($payment->student->user->last_name ?? ''),
                    $payment->payment_method,
                    $payment->amount,
                    $payment->created_at->format('Y-m-d'),
                    $payment->status
                ]);
            }
        }
        
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
/**
 * Export to Excel (simple HTML format)
 */
private function exportToExcel($data, $type, $filename)
{
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($data, $type) {
        $file = fopen('php://output', 'w');
        
        // Write HTML for Excel
        fwrite($file, "<html><body>");
        fwrite($file, "<table border='1'>");
        
        fwrite($file, "<tr><th colspan='2'>" . strtoupper(str_replace('_', ' ', $type)) . " REPORT</th></tr>");
        fwrite($file, "<tr><td colspan='2'>Generated: " . now()->format('Y-m-d H:i:s') . "</td></tr>");
        fwrite($file, "<tr><td colspan='2'>&nbsp;</td></tr>");
        
        if ($type === 'executive') {
            fwrite($file, "<tr><th>Metric</th><th>Value</th></tr>");
            fwrite($file, "<tr><td>Total Invoiced</td><td>" . number_format($data['stats']['total_amount'], 2) . "</td></tr>");
            fwrite($file, "<tr><td>Total Collected</td><td>" . number_format($data['stats']['total_paid'], 2) . "</td></tr>");
            fwrite($file, "<tr><td>Outstanding</td><td>" . number_format($data['stats']['total_balance'], 2) . "</td></tr>");
            fwrite($file, "<tr><td>Collection Rate</td><td>" . $data['stats']['collection_rate'] . "%</td></tr>");
        }
        
        fwrite($file, "</table></body></html>");
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

/**
 * Export to PDF (simplified - returns CSV as fallback)
 */
private function exportToPdf($data, $type, $filename)
{
    // For PDF, you would need a package like dompdf
    // For now, fallback to CSV
    return $this->exportToCsv($data, $type, str_replace('.pdf', '.csv', $filename));
}
    /**
     * Get fee breakdowns
     */
     private function getFeeBreakdowns($academicYearId = null)
    {
        $feeTypes = [
            'tuition' => ['tuition', 'registration'],
            'hostel' => ['hostel'],
            'repeat' => ['repeat_module'],
            'supplementary' => ['supplementary']
        ];
        
        $breakdown = [
            'feeBreakdown' => [],
            'paidBreakdown' => [],
            'balanceBreakdown' => []
        ];
        
        foreach ($feeTypes as $key => $types) {
            $invoiceQuery = Invoice::whereIn('invoice_type', $types);
            
            if ($academicYearId) {
                $invoiceQuery->where('academic_year_id', $academicYearId);
            }
            
            $breakdown['feeBreakdown'][$key] = (clone $invoiceQuery)->sum('total_amount');
            $breakdown['paidBreakdown'][$key] = (clone $invoiceQuery)->sum('paid_amount');
            $breakdown['balanceBreakdown'][$key] = (clone $invoiceQuery)->sum('balance');
        }
        
        $overdueQuery = Invoice::where('due_date', '<', now())
            ->where('payment_status', '!=', 'paid')
            ->where('balance', '>', 0);
            
        if ($academicYearId) {
            $overdueQuery->where('academic_year_id', $academicYearId);
        }
        
        $breakdown['overdueAmount'] = $overdueQuery->sum('balance');
        
        return $breakdown;
    }
}