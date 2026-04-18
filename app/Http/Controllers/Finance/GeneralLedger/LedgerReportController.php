<?php

namespace App\Http\Controllers\Finance\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LedgerReportController extends Controller
{
    /**
     * Display ledger report
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'account_type' => 'nullable|in:asset,liability,equity,revenue,expense'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $accounts = ChartOfAccount::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expenses'
        ];

        $selectedAccount = null;
        $ledgerEntries = collect();
        $openingBalance = 0;
        $closingBalance = 0;

        if ($request->filled('account_id')) {
            $selectedAccount = ChartOfAccount::find($request->account_id);
            
            $fromDate = $request->get('from_date', date('Y-m-01'));
            $toDate = $request->get('to_date', date('Y-m-d'));

            // Get opening balance before from_date
            $openingBalance = $this->getAccountBalanceBeforeDate($selectedAccount, $fromDate);

            // Get ledger entries
            $ledgerEntries = JournalEntryLine::with(['journalEntry' => function($q) {
                    $q->with('creator');
                }])
                ->where('account_id', $selectedAccount->id)
                ->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                    $q->where('entry_date', '>=', $fromDate)
                      ->where('entry_date', '<=', $toDate)
                      ->where('status', 'posted');
                })
                ->orderBy('journalEntry.entry_date')
                ->orderBy('journalEntry.journal_number')
                ->get();

            // Calculate running balance
            $runningBalance = $openingBalance;
            foreach ($ledgerEntries as $entry) {
                // For asset and expense: debit increases, credit decreases
                if ($selectedAccount->account_type === 'asset' || $selectedAccount->account_type === 'expense') {
                    $runningBalance += $entry->debit - $entry->credit;
                } else {
                    // For liability, equity, revenue: credit increases, debit decreases
                    $runningBalance += $entry->credit - $entry->debit;
                }
                $entry->running_balance = $runningBalance;
            }

            $closingBalance = $runningBalance;
        }

        return view('finance.general-ledger.ledger-reports.index', compact(
            'accounts',
            'accountTypes',
            'selectedAccount',
            'ledgerEntries',
            'openingBalance',
            'closingBalance'
        ));
    }

    /**
     * Display general ledger summary
     */
    public function summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'account_type' => 'nullable|in:asset,liability,equity,revenue,expense'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $fromDate = $request->get('from_date', date('Y-m-01'));
        $toDate = $request->get('to_date', date('Y-m-d'));
        $accountType = $request->get('account_type');

        // Build query
        $query = ChartOfAccount::where('is_active', true)
            ->where('is_header', false);

        if ($accountType) {
            $query->where('account_type', $accountType);
        }

        $accounts = $query->orderBy('account_code')->get();

        $summary = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            // Get transaction summary for the period
            $transactions = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                    $q->where('entry_date', '>=', $fromDate)
                      ->where('entry_date', '<=', $toDate)
                      ->where('status', 'posted');
                })
                ->select(
                    DB::raw('SUM(debit) as total_debit'),
                    DB::raw('SUM(credit) as total_credit')
                )
                ->first();

            $periodDebit = $transactions->total_debit ?? 0;
            $periodCredit = $transactions->total_credit ?? 0;

            $openingBalance = $this->getAccountBalanceBeforeDate($account, $fromDate);
            
            // Calculate closing balance
            if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                $closingBalance = $openingBalance + ($periodDebit - $periodCredit);
            } else {
                $closingBalance = $openingBalance + ($periodCredit - $periodDebit);
            }

            $summary[] = [
                'account' => $account,
                'opening_balance' => $openingBalance,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_balance' => $closingBalance
            ];

            $totalDebit += $periodDebit;
            $totalCredit += $periodCredit;
        }

        return view('finance.general-ledger.ledger-reports.summary', compact(
            'summary',
            'fromDate',
            'toDate',
            'accountType',
            'totalDebit',
            'totalCredit'
        ));
    }

    /**
     * Export ledger report
     */
    public function export(Request $request)
    {
        $account = ChartOfAccount::findOrFail($request->account_id);
        $fromDate = $request->get('from_date', date('Y-m-01'));
        $toDate = $request->get('to_date', date('Y-m-d'));

        $openingBalance = $this->getAccountBalanceBeforeDate($account, $fromDate);

        $entries = JournalEntryLine::with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                $q->where('entry_date', '>=', $fromDate)
                  ->where('entry_date', '<=', $toDate)
                  ->where('status', 'posted');
            })
            ->orderBy('journalEntry.entry_date')
            ->get();

        $filename = 'ledger-' . $account->account_code . '-' . $fromDate . '-to-' . $toDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($account, $entries, $fromDate, $toDate, $openingBalance) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['GENERAL LEDGER REPORT']);
            fputcsv($file, ['Account: ' . $account->account_code . ' - ' . $account->account_name]);
            fputcsv($file, ['Period: ' . date('F d, Y', strtotime($fromDate)) . ' to ' . date('F d, Y', strtotime($toDate))]);
            fputcsv($file, ['Opening Balance: ' . number_format($openingBalance, 2)]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'Date',
                'Journal #',
                'Description',
                'Debit',
                'Credit',
                'Balance'
            ]);

            $runningBalance = $openingBalance;

            // Data rows
            foreach ($entries as $entry) {
                if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                    $runningBalance += $entry->debit - $entry->credit;
                } else {
                    $runningBalance += $entry->credit - $entry->debit;
                }

                fputcsv($file, [
                    $entry->journalEntry->entry_date,
                    $entry->journalEntry->journal_number,
                    $entry->description ?? $entry->journalEntry->description,
                    number_format($entry->debit, 2),
                    number_format($entry->credit, 2),
                    number_format($runningBalance, 2)
                ]);
            }

            // Closing balance
            fputcsv($file, []);
            fputcsv($file, ['Closing Balance:', '', '', '', '', number_format($runningBalance, 2)]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get account balance before a specific date
     */
    private function getAccountBalanceBeforeDate($account, $date)
    {
        $balance = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->where('jel.account_id', $account->id)
            ->where('je.entry_date', '<', $date)
            ->where('je.status', 'posted')
            ->select(DB::raw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit'))
            ->first();

        $netChange = ($balance->total_debit ?? 0) - ($balance->total_credit ?? 0);
        
        if ($account->account_type === 'asset' || $account->account_type === 'expense') {
            return $account->opening_balance + $netChange;
        } else {
            return $account->opening_balance - $netChange;
        }
    }
}