<?php

namespace App\Http\Controllers\Finance\Reporting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\BankTransaction;
use App\Models\Department;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INCOME STATEMENT (PROFIT & LOSS)
    |--------------------------------------------------------------------------
    */

    /**
     * Display Income Statement
     */
    public function incomeStatement(Request $request)
    {
        // Set default values FIRST - before validation
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $comparison = $request->get('comparison', 'none');

        // THEN validate with nullable
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'comparison' => 'nullable|in:none,previous_year,budget'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get fiscal years for filter
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();

        // If fiscal year selected, override dates
        if ($request->filled('fiscal_year_id')) {
            $fiscalYear = FiscalYear::find($request->fiscal_year_id);
            if ($fiscalYear) {
                $startDate = $fiscalYear->start_date->format('Y-m-d');
                $endDate = $fiscalYear->end_date->format('Y-m-d');
            }
        }

        // Get revenue accounts
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        // Get expense accounts
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        // Calculate current period amounts
        $revenues = $this->calculateAccountBalances($revenueAccounts, $startDate, $endDate);
        $expenses = $this->calculateAccountBalances($expenseAccounts, $startDate, $endDate);

        // Calculate totals
        $totalRevenue = $revenues->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netIncome = $totalRevenue - $totalExpense;

        // Get comparison data if requested
        $comparisonData = null;
        if ($comparison !== 'none') {
            $comparisonData = $this->getComparisonData($startDate, $endDate, $comparison);
        }

        return view('finance.reporting.income-statement', compact(
            'revenues',
            'expenses',
            'totalRevenue',
            'totalExpense',
            'netIncome',
            'startDate',
            'endDate',
            'comparison',
            'comparisonData',
            'fiscalYears'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | BALANCE SHEET
    |--------------------------------------------------------------------------
    */

    /**
     * Display Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        // Set default values FIRST
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $showComparison = $request->boolean('show_comparison', false);

        // THEN validate with nullable
        $validator = Validator::make($request->all(), [
            'as_of_date' => 'nullable|date',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'show_comparison' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get fiscal years for filter
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();

        // If fiscal year selected, override as_of_date
        if ($request->filled('fiscal_year_id')) {
            $fiscalYear = FiscalYear::find($request->fiscal_year_id);
            if ($fiscalYear) {
                $asOfDate = $fiscalYear->end_date->format('Y-m-d');
            }
        }

        // Get asset accounts
        $assetAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        // Get liability accounts
        $liabilityAccounts = ChartOfAccount::where('account_type', 'liability')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        // Get equity accounts
        $equityAccounts = ChartOfAccount::where('account_type', 'equity')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        // Calculate balances as of date
        $assets = $this->calculateBalancesAsOfDate($assetAccounts, $asOfDate);
        $liabilities = $this->calculateBalancesAsOfDate($liabilityAccounts, $asOfDate);
        $equity = $this->calculateBalancesAsOfDate($equityAccounts, $asOfDate);

        // Calculate totals
        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        // Get previous period for comparison
        $previousAssets = null;
        $previousLiabilities = null;
        $previousEquity = null;
        $previousTotalAssets = 0;
        $previousTotalLiabilities = 0;
        $previousTotalEquity = 0;

        if ($showComparison) {
            $previousDate = Carbon::parse($asOfDate)->subYear()->format('Y-m-d');
            $previousAssets = $this->calculateBalancesAsOfDate($assetAccounts, $previousDate);
            $previousLiabilities = $this->calculateBalancesAsOfDate($liabilityAccounts, $previousDate);
            $previousEquity = $this->calculateBalancesAsOfDate($equityAccounts, $previousDate);
            
            $previousTotalAssets = $previousAssets->sum('balance');
            $previousTotalLiabilities = $previousLiabilities->sum('balance');
            $previousTotalEquity = $previousEquity->sum('balance');
        }

        return view('finance.reporting.balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'totalLiabilitiesEquity',
            'asOfDate',
            'showComparison',
            'previousAssets',
            'previousLiabilities',
            'previousEquity',
            'previousTotalAssets',
            'previousTotalLiabilities',
            'previousTotalEquity',
            'fiscalYears'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CASH FLOW STATEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Display Cash Flow Statement
     */
    public function cashFlowStatement(Request $request)
    {
        // Set default values FIRST
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $method = $request->get('method', 'indirect');

        // THEN validate with nullable
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'method' => 'nullable|in:direct,indirect'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get fiscal years for filter
        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();

        // Get cash accounts
        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('category', 'current_asset')
            ->where(function($q) {
                $q->where('account_name', 'LIKE', '%Cash%')
                  ->orWhere('account_name', 'LIKE', '%Bank%')
                  ->orWhere('account_name', 'LIKE', '%M-Pesa%')
                  ->orWhere('account_name', 'LIKE', '%Petty%');
            })
            ->where('is_active', true)
            ->pluck('id');

        if ($method === 'indirect') {
            // INDIRECT METHOD - Start with net income
            $cashFlow = $this->getIndirectCashFlow($startDate, $endDate, $cashAccounts);
        } else {
            // DIRECT METHOD - Actual cash receipts and payments
            $cashFlow = $this->getDirectCashFlow($startDate, $endDate, $cashAccounts);
        }

        return view('finance.reporting.cash-flow', compact(
            'cashFlow',
            'startDate',
            'endDate',
            'method',
            'fiscalYears'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT REPORTS
    |--------------------------------------------------------------------------
    */

    /**
     * Display Department Reports
     */
    public function departmentReports(Request $request)
    {
        // Set default values FIRST
        $departmentId = $request->get('department_id');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'summary');

        // THEN validate with nullable
        $validator = Validator::make($request->all(), [
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'report_type' => 'nullable|in:expenses,budget,summary'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get all departments
        $departments = Department::orderBy('name')->get();

        $departmentData = [];
        $summary = [];

        if ($departmentId) {
            // Get specific department report
            $department = Department::find($departmentId);
            $departmentData = $this->getDepartmentReport($departmentId, $startDate, $endDate, $reportType);
        } else {
            // Get summary for all departments
            $summary = $this->getAllDepartmentsSummary($startDate, $endDate);
        }

        return view('finance.reporting.department-reports', compact(
            'departments',
            'departmentId',
            'departmentData',
            'summary',
            'startDate',
            'endDate',
            'reportType'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Export Income Statement
     */
    public function exportIncomeStatement(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $format = $request->get('format', 'pdf');

        // Validate
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'nullable|in:pdf,csv'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // Get data
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $revenues = $this->calculateAccountBalances($revenueAccounts, $startDate, $endDate);
        $expenses = $this->calculateAccountBalances($expenseAccounts, $startDate, $endDate);
        
        $totalRevenue = $revenues->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netIncome = $totalRevenue - $totalExpense;

        if ($format === 'pdf') {
            $pdf = \PDF::loadView('finance.reporting.exports.income-statement-pdf', compact(
                'revenues', 'expenses', 'totalRevenue', 'totalExpense', 'netIncome', 'startDate', 'endDate'
            ));
            return $pdf->download('income-statement-' . $startDate . '-to-' . $endDate . '.pdf');
        }

        // CSV Export
        $filename = 'income-statement-' . $startDate . '-to-' . $endDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($revenues, $expenses, $totalRevenue, $totalExpense, $netIncome) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['INCOME STATEMENT']);
            fputcsv($file, []);
            fputcsv($file, ['REVENUE']);
            
            foreach ($revenues as $revenue) {
                fputcsv($file, [$revenue->account_name, number_format($revenue->amount, 2)]);
            }
            fputcsv($file, ['Total Revenue', number_format($totalRevenue, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['EXPENSES']);
            foreach ($expenses as $expense) {
                fputcsv($file, [$expense->account_name, number_format($expense->amount, 2)]);
            }
            fputcsv($file, ['Total Expenses', number_format($totalExpense, 2)]);
            fputcsv($file, []);
            
            fputcsv($file, ['NET INCOME', number_format($netIncome, 2)]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate account balances for a period
     */
    private function calculateAccountBalances($accounts, $startDate, $endDate)
    {
        foreach ($accounts as $account) {
            $lines = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                    $q->where('entry_date', '>=', $startDate)
                      ->where('entry_date', '<=', $endDate)
                      ->where('status', 'posted');
                })
                ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
                ->first();

            $account->amount = ($lines->total_debit ?? 0) - ($lines->total_credit ?? 0);
            
            // For revenue accounts, credit increases balance
            if ($account->account_type === 'revenue') {
                $account->amount = -$account->amount;
            }
        }

        return $accounts->filter(function($account) {
            return abs($account->amount) > 0.01; // Filter out zero amounts
        });
    }

    /**
     * Calculate balances as of a specific date
     */
    private function calculateBalancesAsOfDate($accounts, $asOfDate)
    {
        foreach ($accounts as $account) {
            $lines = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) use ($asOfDate) {
                    $q->where('entry_date', '<=', $asOfDate)
                      ->where('status', 'posted');
                })
                ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
                ->first();

            $netChange = ($lines->total_debit ?? 0) - ($lines->total_credit ?? 0);
            
            if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                $account->balance = $account->opening_balance + $netChange;
            } else {
                $account->balance = $account->opening_balance - $netChange;
            }
        }

        return $accounts->filter(function($account) {
            return abs($account->balance) > 0.01; // Filter out zero balances
        });
    }

    /**
     * Get indirect cash flow data
     */
    private function getIndirectCashFlow($startDate, $endDate, $cashAccounts)
    {
        // Calculate net income
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')->pluck('id');
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')->pluck('id');

        $totalRevenue = JournalEntryLine::whereIn('account_id', $revenueAccounts)
            ->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->sum('credit');

        $totalExpense = JournalEntryLine::whereIn('account_id', $expenseAccounts)
            ->whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->sum('debit');

        $netIncome = $totalRevenue - $totalExpense;

        // Calculate changes in working capital
        $receivables = $this->getAccountBalanceChange('accounts_receivable', $startDate, $endDate);
        $payables = $this->getAccountBalanceChange('accounts_payable', $startDate, $endDate);
        $inventory = $this->getAccountBalanceChange('inventory', $startDate, $endDate);
        $prepaids = $this->getAccountBalanceChange('prepaid_expenses', $startDate, $endDate);
        $accruals = $this->getAccountBalanceChange('accrued_liabilities', $startDate, $endDate);

        // Get investing activities
        $investing = $this->getInvestingActivities($startDate, $endDate, $cashAccounts);

        // Get financing activities
        $financing = $this->getFinancingActivities($startDate, $endDate, $cashAccounts);

        // Calculate net cash flow
        $operatingCashFlow = $netIncome 
            - $receivables['change'] 
            + $payables['change'] 
            - $inventory['change'] 
            - $prepaids['change'] 
            + $accruals['change'];

        $investingCashFlow = $investing['net'];
        $financingCashFlow = $financing['net'];

        $netCashFlow = $operatingCashFlow + $investingCashFlow + $financingCashFlow;

        // Get opening and closing cash
        $openingCash = $this->getOpeningCashBalance($startDate, $cashAccounts);
        $closingCash = $openingCash + $netCashFlow;

        return [
            'net_income' => $netIncome,
            'operating' => [
                'net' => $operatingCashFlow,
                'receivables' => $receivables,
                'payables' => $payables,
                'inventory' => $inventory,
                'prepaids' => $prepaids,
                'accruals' => $accruals,
            ],
            'investing' => $investing,
            'financing' => $financing,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'net_cash_flow' => $netCashFlow
        ];
    }

    /**
     * Get direct cash flow data
     */
    private function getDirectCashFlow($startDate, $endDate, $cashAccounts)
    {
        // Cash receipts from customers
        $cashReceipts = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'revenue');
            })
            ->sum('credit');

        // Cash payments to suppliers
        $cashPayments = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'expense');
            })
            ->sum('debit');

        // Cash payments for operating expenses
        $operatingExpenses = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'expense')
                  ->where('category', 'operating_expense');
            })
            ->sum('debit');

        // Get investing activities
        $investing = $this->getInvestingActivities($startDate, $endDate, $cashAccounts);

        // Get financing activities
        $financing = $this->getFinancingActivities($startDate, $endDate, $cashAccounts);

        // Calculate net cash flow
        $operatingCashFlow = $cashReceipts - $cashPayments - $operatingExpenses;
        $investingCashFlow = $investing['net'];
        $financingCashFlow = $financing['net'];

        $netCashFlow = $operatingCashFlow + $investingCashFlow + $financingCashFlow;

        // Get opening and closing cash
        $openingCash = $this->getOpeningCashBalance($startDate, $cashAccounts);
        $closingCash = $openingCash + $netCashFlow;

        return [
            'operating' => [
                'net' => $operatingCashFlow,
                'receipts' => $cashReceipts,
                'payments' => $cashPayments,
                'operating_expenses' => $operatingExpenses,
            ],
            'investing' => $investing,
            'financing' => $financing,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'net_cash_flow' => $netCashFlow
        ];
    }

    /**
     * Get account balance change
     */
    private function getAccountBalanceChange($accountCode, $startDate, $endDate)
    {
        $account = ChartOfAccount::where('account_code', 'LIKE', "%$accountCode%")->first();
        
        if (!$account) {
            return ['opening' => 0, 'closing' => 0, 'change' => 0];
        }

        $opening = $this->getAccountBalanceAsOf($account->id, Carbon::parse($startDate)->subDay());
        $closing = $this->getAccountBalanceAsOf($account->id, $endDate);
        
        return [
            'opening' => $opening,
            'closing' => $closing,
            'change' => $closing - $opening
        ];
    }

    /**
     * Get account balance as of date
     */
    private function getAccountBalanceAsOf($accountId, $date)
    {
        $lines = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function($q) use ($date) {
                $q->where('entry_date', '<=', $date)
                  ->where('status', 'posted');
            })
            ->select(DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->first();

        return ($lines->total_debit ?? 0) - ($lines->total_credit ?? 0);
    }

    /**
     * Get investing activities
     */
    private function getInvestingActivities($startDate, $endDate, $cashAccounts)
    {
        // Purchase of fixed assets
        $assetPurchases = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'asset')
                  ->where('category', 'fixed_asset');
            })
            ->sum('debit');

        // Sale of fixed assets
        $assetSales = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'asset')
                  ->where('category', 'fixed_asset');
            })
            ->sum('credit');

        return [
            'purchases' => $assetPurchases,
            'sales' => $assetSales,
            'net' => $assetSales - $assetPurchases
        ];
    }

    /**
     * Get financing activities
     */
    private function getFinancingActivities($startDate, $endDate, $cashAccounts)
    {
        // Loans received
        $loansReceived = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'liability')
                  ->where('category', 'long_term_liability');
            })
            ->sum('credit');

        // Loans repaid
        $loansRepaid = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'liability')
                  ->where('category', 'long_term_liability');
            })
            ->sum('debit');

        // Capital contributions
        $capitalIn = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'equity');
            })
            ->sum('credit');

        // Dividends paid
        $dividendsOut = JournalEntryLine::whereHas('journalEntry', function($q) use ($startDate, $endDate) {
                $q->where('entry_date', '>=', $startDate)
                  ->where('entry_date', '<=', $endDate)
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->where('account_type', 'equity')
                  ->where('account_name', 'LIKE', '%Dividend%');
            })
            ->sum('debit');

        return [
            'loans_received' => $loansReceived,
            'loans_repaid' => $loansRepaid,
            'capital_in' => $capitalIn,
            'dividends_out' => $dividendsOut,
            'net' => ($loansReceived + $capitalIn) - ($loansRepaid + $dividendsOut)
        ];
    }

    /**
     * Get opening cash balance
     */
    private function getOpeningCashBalance($startDate, $cashAccounts)
    {
        $previousDay = Carbon::parse($startDate)->subDay();

        return BankTransaction::whereIn('bank_account_id', $cashAccounts)
            ->where('transaction_date', '<', $startDate)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->first()?->balance_after ?? 0;
    }

    /**
     * Get comparison data
     */
    private function getComparisonData($startDate, $endDate, $comparison)
    {
        if ($comparison === 'previous_year') {
            $yearDiff = Carbon::parse($endDate)->year - Carbon::parse($startDate)->year;
            $prevStartDate = Carbon::parse($startDate)->subYears($yearDiff)->format('Y-m-d');
            $prevEndDate = Carbon::parse($endDate)->subYears($yearDiff)->format('Y-m-d');
        } else {
            // Budget comparison - would need budget data
            return null;
        }

        // Get revenue accounts for comparison
        $revenueAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $prevRevenues = $this->calculateAccountBalances($revenueAccounts, $prevStartDate, $prevEndDate);
        $prevExpenses = $this->calculateAccountBalances($expenseAccounts, $prevStartDate, $prevEndDate);

        $prevTotalRevenue = $prevRevenues->sum('amount');
        $prevTotalExpense = $prevExpenses->sum('amount');
        $prevNetIncome = $prevTotalRevenue - $prevTotalExpense;

        return [
            'start_date' => $prevStartDate,
            'end_date' => $prevEndDate,
            'revenues' => $prevRevenues,
            'expenses' => $prevExpenses,
            'total_revenue' => $prevTotalRevenue,
            'total_expense' => $prevTotalExpense,
            'net_income' => $prevNetIncome
        ];
    }

    /**
     * Get department report
     */
    private function getDepartmentReport($departmentId, $startDate, $endDate, $reportType)
    {
        // This depends on how departments are linked to accounts
        // You might have department_id on ChartOfAccount or need custom mapping
        
        $department = Department::find($departmentId);
        
        // Get expense accounts for this department
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->where('department_id', $departmentId) // Assuming department_id exists on accounts
            ->where('is_active', true)
            ->get();

        $expenses = $this->calculateAccountBalances($expenseAccounts, $startDate, $endDate);
        $totalExpense = $expenses->sum('amount');

        // Get budget data if available
        $budget = null;
        if ($reportType === 'budget') {
            $budget = DB::table('department_budgets')
                ->where('department_id', $departmentId)
                ->whereBetween('period_start', [$startDate, $endDate])
                ->sum('amount');
        }

        return [
            'department' => $department,
            'expenses' => $expenses,
            'total_expense' => $totalExpense,
            'budget' => $budget,
            'variance' => $budget ? $totalExpense - $budget : null,
            'variance_percentage' => $budget && $budget > 0 ? round(($totalExpense - $budget) / $budget * 100, 2) : 0
        ];
    }

    /**
     * Get all departments summary
     */
    private function getAllDepartmentsSummary($startDate, $endDate)
    {
        $departments = Department::all();
        $summary = [];

        foreach ($departments as $dept) {
            $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
                ->where('department_id', $dept->id)
                ->where('is_active', true)
                ->get();

            $expenses = $this->calculateAccountBalances($expenseAccounts, $startDate, $endDate);
            $totalExpense = $expenses->sum('amount');

            $summary[] = [
                'department' => $dept,
                'total_expense' => $totalExpense,
                'expense_count' => $expenses->count()
            ];
        }

        return $summary;
    }
}