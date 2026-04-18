<?php

namespace App\Http\Controllers\Finance\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\AccountBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrialBalanceController extends Controller
{
    /**
     * Display trial balance
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'as_of_date' => 'nullable|date',
            'account_type' => 'nullable|in:asset,liability,equity,revenue,expense',
            'show_zero_balances' => 'nullable|boolean',
            'level' => 'nullable|integer|min:1|max:3'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $asOfDate = $request->get('as_of_date', date('Y-m-d'));
        $accountType = $request->get('account_type');
        $showZeroBalances = $request->boolean('show_zero_balances', true);
        $level = $request->get('level');

        // Build query
        $query = ChartOfAccount::query();

        if ($accountType) {
            $query->where('account_type', $accountType);
        }

        if ($level) {
            $query->where('level', $level);
        }

        if (!$showZeroBalances) {
            $query->where('current_balance', '!=', 0);
        }

        $accounts = $query->orderBy('account_code')->get();

        // Calculate balances as of date (if not using current)
        if ($asOfDate !== date('Y-m-d')) {
            $this->calculateBalancesAsOfDate($accounts, $asOfDate);
        }

        // Prepare trial balance data
        $trialBalance = [
            'accounts' => $accounts,
            'totals' => [
                'debit' => 0,
                'credit' => 0
            ],
            'by_type' => []
        ];

        // Group by account type and calculate totals
        foreach ($accounts as $account) {
            $balance = $account->current_balance;
            
            // Determine if balance is debit or credit based on account type
            if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                if ($balance > 0) {
                    $trialBalance['totals']['debit'] += $balance;
                    $account->trial_balance_debit = $balance;
                    $account->trial_balance_credit = 0;
                } else {
                    $trialBalance['totals']['credit'] += abs($balance);
                    $account->trial_balance_debit = 0;
                    $account->trial_balance_credit = abs($balance);
                }
            } else {
                if ($balance > 0) {
                    $trialBalance['totals']['credit'] += $balance;
                    $account->trial_balance_debit = 0;
                    $account->trial_balance_credit = $balance;
                } else {
                    $trialBalance['totals']['debit'] += abs($balance);
                    $account->trial_balance_debit = abs($balance);
                    $account->trial_balance_credit = 0;
                }
            }

            // Group by account type
            if (!isset($trialBalance['by_type'][$account->account_type])) {
                $trialBalance['by_type'][$account->account_type] = [
                    'name' => $account->type_name,
                    'debit' => 0,
                    'credit' => 0,
                    'count' => 0
                ];
            }

            $trialBalance['by_type'][$account->account_type]['debit'] += $account->trial_balance_debit;
            $trialBalance['by_type'][$account->account_type]['credit'] += $account->trial_balance_credit;
            $trialBalance['by_type'][$account->account_type]['count']++;
        }

        // Check if trial balance is balanced
        $trialBalance['is_balanced'] = abs($trialBalance['totals']['debit'] - $trialBalance['totals']['credit']) < 0.01;

        return view('finance.general-ledger.trial-balance.index', compact('trialBalance', 'asOfDate', 'accountType', 'showZeroBalances', 'level'));
    }

    /**
     * Export trial balance
     */
    public function export(Request $request)
    {
        $asOfDate = $request->get('as_of_date', date('Y-m-d'));
        
        // Get trial balance data
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        
        if ($asOfDate !== date('Y-m-d')) {
            $this->calculateBalancesAsOfDate($accounts, $asOfDate);
        }

        $filename = 'trial-balance-' . $asOfDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($accounts, $asOfDate) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['TRIAL BALANCE AS OF ' . date('F d, Y', strtotime($asOfDate))]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'Account Code',
                'Account Name',
                'Account Type',
                'Debit',
                'Credit'
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            // Data rows
            foreach ($accounts as $account) {
                if ($account->current_balance == 0) continue;

                $debit = 0;
                $credit = 0;

                if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                    if ($account->current_balance > 0) {
                        $debit = $account->current_balance;
                        $totalDebit += $debit;
                    } else {
                        $credit = abs($account->current_balance);
                        $totalCredit += $credit;
                    }
                } else {
                    if ($account->current_balance > 0) {
                        $credit = $account->current_balance;
                        $totalCredit += $credit;
                    } else {
                        $debit = abs($account->current_balance);
                        $totalDebit += $debit;
                    }
                }

                if ($debit > 0 || $credit > 0) {
                    fputcsv($file, [
                        $account->account_code,
                        $account->account_name,
                        $account->type_name,
                        number_format($debit, 2),
                        number_format($credit, 2)
                    ]);
                }
            }

            // Totals
            fputcsv($file, []);
            fputcsv($file, [
                'TOTALS',
                '',
                '',
                number_format($totalDebit, 2),
                number_format($totalCredit, 2)
            ]);

            // Check if balanced
            $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
            fputcsv($file, [
                'STATUS: ' . ($isBalanced ? 'BALANCED' : 'NOT BALANCED'),
                '',
                '',
                '',
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print trial balance
     */
    public function print(Request $request)
    {
        $asOfDate = $request->get('as_of_date', date('Y-m-d'));
        
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        
        if ($asOfDate !== date('Y-m-d')) {
            $this->calculateBalancesAsOfDate($accounts, $asOfDate);
        }

        return view('finance.general-ledger.trial-balance.print', compact('accounts', 'asOfDate'));
    }

    /**
     * Calculate account balances as of a specific date
     */
    private function calculateBalancesAsOfDate($accounts, $asOfDate)
    {
        foreach ($accounts as $account) {
            // Get the balance record for the date
            $balanceRecord = AccountBalance::where('account_id', $account->id)
                ->where('balance_date', $asOfDate)
                ->first();

            if ($balanceRecord) {
                $account->current_balance = $balanceRecord->closing_balance;
            } else {
                // Calculate balance from journal entries up to the date
                $balance = DB::table('journal_entry_lines as jel')
                    ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
                    ->where('jel.account_id', $account->id)
                    ->where('je.entry_date', '<=', $asOfDate)
                    ->where('je.status', 'posted')
                    ->select(DB::raw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit'))
                    ->first();

                $netChange = ($balance->total_debit ?? 0) - ($balance->total_credit ?? 0);
                
                if ($account->account_type === 'asset' || $account->account_type === 'expense') {
                    $account->current_balance = $account->opening_balance + $netChange;
                } else {
                    $account->current_balance = $account->opening_balance - $netChange;
                }
            }
        }
    }
}