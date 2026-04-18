<?php
// app/Http/Controllers/Finance/Bank/BankReconciliationController.php

namespace App\Http\Controllers\Finance\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BankReconciliationController extends Controller
{
    /**
     * Display list of reconciliations
     */
    public function index(Request $request)
    {
        $query = BankReconciliation::with(['bankAccount', 'creator']);

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->whereMonth('statement_date', $request->month)
                  ->whereYear('statement_date', $request->year ?? date('Y'));
        }

        if ($request->filled('year')) {
            $query->whereYear('statement_date', $request->year);
        }

        $reconciliations = $query->orderBy('statement_date', 'desc')
            ->paginate(15);

        $bankAccounts = BankAccount::where('is_active', true)->get();
        $years = range(date('Y'), date('Y') - 3);
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return view('finance.bank.reconciliation.index', compact(
            'reconciliations', 'bankAccounts', 'years', 'months'
        ));
    }

    /**
     * Show form to create new reconciliation
     */
    public function create(Request $request)
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();
        
        $selectedAccount = $request->get('bank_account_id');
        $selectedMonth = $request->get('month', date('m'));
        $selectedYear = $request->get('year', date('Y'));

        $account = null;
        $transactions = collect();
        $openingBalance = 0;
        $closingBalance = 0;

        if ($selectedAccount) {
            $account = BankAccount::find($selectedAccount);
            
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // Get opening balance (before this month)
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

            // Get transactions for the month
            $transactions = BankTransaction::where('bank_account_id', $selectedAccount)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            // Calculate closing balance
            $runningBalance = $openingBalance;
            foreach ($transactions as $txn) {
                if (in_array($txn->transaction_type, ['deposit', 'opening_balance']) ||
                    ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'incoming')) {
                    $runningBalance += $txn->amount;
                } else {
                    $runningBalance -= $txn->amount;
                }
            }
            $closingBalance = $runningBalance;

            // Check if reconciliation already exists
            $existing = BankReconciliation::where('bank_account_id', $selectedAccount)
                ->whereMonth('statement_date', $selectedMonth)
                ->whereYear('statement_date', $selectedYear)
                ->exists();

            if ($existing) {
                return redirect()->route('finance.bank.reconciliation.index')
                    ->with('error', 'Reconciliation already exists for this period');
            }
        }

        return view('finance.bank.reconciliation.create', compact(
            'bankAccounts', 'selectedAccount', 'selectedMonth', 'selectedYear',
            'account', 'transactions', 'openingBalance', 'closingBalance'
        ));
    }

    /**
     * Store new reconciliation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'statement_date' => 'required|date',
            'statement_balance' => 'required|numeric|min:0',
            'system_balance' => 'required|numeric',
            'transactions' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->statement_date)->startOfMonth();
            $endDate = Carbon::parse($request->statement_date)->endOfMonth();

            // Check for existing reconciliation
            $existing = BankReconciliation::where('bank_account_id', $request->bank_account_id)
                ->whereMonth('statement_date', $startDate->month)
                ->whereYear('statement_date', $startDate->year)
                ->exists();

            if ($existing) {
                throw new \Exception('Reconciliation already exists for this period');
            }

            $difference = $request->statement_balance - $request->system_balance;

            // Create reconciliation
            $reconciliation = BankReconciliation::create([
                'bank_account_id' => $request->bank_account_id,
                'reconciliation_number' => $this->generateReconciliationNumber(),
                'statement_date' => $request->statement_date,
                'statement_balance' => $request->statement_balance,
                'system_balance' => $request->system_balance,
                'difference' => $difference,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'in_progress',
                'notes' => $request->notes,
                'created_by' => Auth::id()
            ]);

            // Create reconciliation entries
            foreach ($request->transactions as $txnData) {
                if (isset($txnData['selected']) && $txnData['selected']) {
                    BankReconciliationEntry::create([
                        'bank_reconciliation_id' => $reconciliation->id,
                        'bank_transaction_id' => $txnData['id'],
                        'matched' => true,
                        'notes' => $txnData['notes'] ?? null
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('finance.bank.reconciliation.show', $reconciliation->id)
                ->with('success', 'Reconciliation created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reconciliation creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create reconciliation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show reconciliation details
     */
    public function show($id)
    {
        $reconciliation = BankReconciliation::with([
            'bankAccount', 
            'creator', 
            'completer',
            'entries.transaction'
        ])->findOrFail($id);

        // Get all transactions for the period
        $transactions = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->whereBetween('transaction_date', [$reconciliation->start_date, $reconciliation->end_date])
            ->where('status', 'completed')
            ->orderBy('transaction_date')
            ->get();

        // Mark matched transactions
        $matchedIds = $reconciliation->entries->pluck('bank_transaction_id')->toArray();
        foreach ($transactions as $txn) {
            $txn->is_matched = in_array($txn->id, $matchedIds);
        }

        // Calculate running balance
        $openingBalance = $this->getOpeningBalance(
            $reconciliation->bank_account_id, 
            $reconciliation->start_date
        );

        $runningBalance = $openingBalance;
        foreach ($transactions as $txn) {
            if (in_array($txn->transaction_type, ['deposit', 'opening_balance']) ||
                ($txn->transaction_type == 'transfer' && ($txn->metadata['transfer_type'] ?? '') == 'incoming')) {
                $runningBalance += $txn->amount;
            } else {
                $runningBalance -= $txn->amount;
            }
            $txn->running_balance = $runningBalance;
        }

        return view('finance.bank.reconciliation.show', compact(
            'reconciliation', 'transactions', 'openingBalance'
        ));
    }

    /**
     * Complete reconciliation
     */
    public function complete($id, Request $request)
    {
        try {
            DB::beginTransaction();

            $reconciliation = BankReconciliation::findOrFail($id);

            if ($reconciliation->status != 'in_progress') {
                throw new \Exception('Reconciliation cannot be completed');
            }

            // Mark all matched transactions as reconciled
            $entryIds = $reconciliation->entries()->pluck('bank_transaction_id');
            
            BankTransaction::whereIn('id', $entryIds)->update([
                'status' => 'reconciled',
                'reconciled_at' => now(),
                'reconciled_by' => Auth::id()
            ]);

            // Update reconciliation
            $reconciliation->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.bank.reconciliation.show', $id)
                ->with('success', 'Reconciliation completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reconciliation completion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to complete reconciliation: ' . $e->getMessage());
        }
    }

    /**
     * Get opening balance before date
     */
    private function getOpeningBalance($accountId, $date)
    {
        return BankTransaction::where('bank_account_id', $accountId)
            ->where('transaction_date', '<', $date)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN transaction_type IN ('deposit', 'opening_balance', 'transfer') 
                AND JSON_EXTRACT(metadata, '$.transfer_type') != 'outgoing' 
                THEN amount 
                WHEN transaction_type IN ('withdrawal', 'transfer') 
                AND JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'
                THEN -amount 
                ELSE 0 END"));
    }

    /**
     * Generate reconciliation number
     */
    private function generateReconciliationNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastRec = BankReconciliation::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRec) {
            $lastNumber = intval(substr($lastRec->reconciliation_number, -4));
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "REC-{$year}{$month}-{$sequence}";
    }
}