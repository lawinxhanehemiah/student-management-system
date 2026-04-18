<?php
// app/Http/Controllers/Finance/Bank/CashbookController.php

namespace App\Http\Controllers\Finance\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashbookController extends Controller
{
    /**
     * Display cashbook
     */
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $selectedAccount = $request->get('bank_account_id');

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($selectedAccount) {
            $account = BankAccount::find($selectedAccount);

            // Get opening balance
            $openingBalance = BankTransaction::where('bank_account_id', $selectedAccount)
                ->where('transaction_date', '<', $startDate)
                ->where('status', 'completed')
                ->sum(DB::raw("CASE WHEN transaction_type IN ('deposit', 'opening_balance', 'transfer') 
                    AND JSON_EXTRACT(metadata, '$.transfer_type') != 'outgoing' 
                    THEN amount 
                    WHEN transaction_type IN ('withdrawal', 'transfer') 
                    AND JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'
                    THEN -amount 
                    ELSE 0 END"));

            // Get transactions for period
            $transactions = BankTransaction::where('bank_account_id', $selectedAccount)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            // Calculate running balance
            $runningBalance = $openingBalance;
            foreach ($transactions as $txn) {
                if (in_array($txn->transaction_type, ['deposit', 'opening_balance']) ||
                    ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'incoming')) {
                    $runningBalance += $txn->amount;
                    $txn->debit = $txn->amount;
                    $txn->credit = 0;
                } else {
                    $runningBalance -= $txn->amount;
                    $txn->debit = 0;
                    $txn->credit = $txn->amount;
                }
                $txn->running_balance = $runningBalance;
            }

            // Calculate totals
            $totals = [
                'debit' => $transactions->sum('debit'),
                'credit' => $transactions->sum('credit'),
                'opening' => $openingBalance,
                'closing' => $runningBalance
            ];

        } else {
            $account = null;
            $transactions = collect();
            $totals = ['debit' => 0, 'credit' => 0, 'opening' => 0, 'closing' => 0];
        }

        return view('finance.bank.cashbook.index', compact(
            'bankAccounts', 'selectedAccount', 'account',
            'transactions', 'totals', 'startDate', 'endDate'
        ));
    }

    /**
     * Export cashbook
     */
    public function export(Request $request)
    {
        $selectedAccount = $request->get('bank_account_id');
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if (!$selectedAccount) {
            return redirect()->back()->with('error', 'Please select a bank account');
        }

        $account = BankAccount::find($selectedAccount);

        // Get opening balance
        $openingBalance = BankTransaction::where('bank_account_id', $selectedAccount)
            ->where('transaction_date', '<', $startDate)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN transaction_type IN ('deposit', 'opening_balance', 'transfer') 
                AND JSON_EXTRACT(metadata, '$.transfer_type') != 'outgoing' 
                THEN amount 
                WHEN transaction_type IN ('withdrawal', 'transfer') 
                AND JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'
                THEN -amount 
                ELSE 0 END"));

        $transactions = BankTransaction::where('bank_account_id', $selectedAccount)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $filename = 'cashbook-' . $account->account_number . '-' . $startDate . '-to-' . $endDate . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($account, $transactions, $openingBalance, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['CASHBOOK']);
            fputcsv($file, ['Account: ' . $account->bank_name . ' - ' . $account->account_name . ' (' . $account->account_number . ')']);
            fputcsv($file, ['Period: ' . $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Opening Balance: ' . number_format($openingBalance, 2)]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'Date',
                'Transaction #',
                'Description',
                'Reference',
                'Debit (TZS)',
                'Credit (TZS)',
                'Balance (TZS)'
            ]);

            $runningBalance = $openingBalance;

            // Opening balance row
            fputcsv($file, [
                $startDate,
                '',
                'Opening Balance',
                '',
                '',
                '',
                number_format($runningBalance, 2)
            ]);

            // Data rows
            foreach ($transactions as $txn) {
                if (in_array($txn->transaction_type, ['deposit', 'opening_balance']) ||
                    ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'incoming')) {
                    $runningBalance += $txn->amount;
                    $debit = $txn->amount;
                    $credit = 0;
                } else {
                    $runningBalance -= $txn->amount;
                    $debit = 0;
                    $credit = $txn->amount;
                }

                fputcsv($file, [
                    $txn->transaction_date,
                    $txn->transaction_number,
                    $txn->description,
                    $txn->metadata['reference'] ?? '',
                    $debit > 0 ? number_format($debit, 2) : '',
                    $credit > 0 ? number_format($credit, 2) : '',
                    number_format($runningBalance, 2)
                ]);
            }

            // Closing balance
            fputcsv($file, []);
            fputcsv($file, [
                '',
                '',
                'Closing Balance',
                '',
                '',
                '',
                number_format($runningBalance, 2)
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}