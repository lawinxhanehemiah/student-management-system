<?php
// app/Http/Controllers/Finance/Bank/BankAccountController.php

namespace App\Http\Controllers\Finance\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Display list of bank accounts
     */
    public function index(Request $request)
    {
        $query = BankAccount::with('creator');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_name', 'LIKE', "%{$search}%")
                  ->orWhere('account_number', 'LIKE', "%{$search}%")
                  ->orWhere('bank_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        $accounts = $query->orderBy('is_default', 'desc')
            ->orderBy('bank_name')
            ->paginate(15);

        $currencies = ['TZS', 'USD', 'KES', 'EUR', 'GBP'];
        $totalBalance = BankAccount::where('is_active', true)->sum('current_balance');

        return view('finance.bank.accounts.index', compact('accounts', 'currencies', 'totalBalance'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $currencies = ['TZS', 'USD', 'KES', 'EUR', 'GBP'];
        return view('finance.bank.accounts.create', compact('currencies'));
    }

    /**
     * Store new bank account
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // If this is default, remove other defaults
            if ($request->boolean('is_default')) {
                BankAccount::where('is_default', true)->update(['is_default' => false]);
            }

            $account = BankAccount::create([
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'branch' => $request->branch,
                'swift_code' => $request->swift_code,
                'currency' => $request->currency,
                'opening_balance' => $request->opening_balance ?? 0,
                'current_balance' => $request->opening_balance ?? 0,
                'is_active' => true,
                'is_default' => $request->boolean('is_default', false),
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // Create opening balance transaction
            if ($request->filled('opening_balance') && $request->opening_balance > 0) {
                BankTransaction::create([
                    'bank_account_id' => $account->id,
                    'transaction_number' => $this->generateTransactionNumber('OPEN'),
                    'transaction_date' => now(),
                    'transaction_type' => 'opening_balance',
                    'amount' => $request->opening_balance,
                    'balance_before' => 0,
                    'balance_after' => $request->opening_balance,
                    'description' => 'Opening balance for ' . $account->account_name,
                    'status' => 'completed',
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            return redirect()->route('finance.bank.accounts.show', $account->id)
                ->with('success', 'Bank account created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bank account creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create bank account: ' . $e->getMessage())
                ->withInput();
        }
    }

  /**
 * Display the specified bank account.
 */
public function show($id)
{
    try {
        // Log the ID for debugging
        \Log::info('===== BANK ACCOUNT SHOW METHOD CALLED =====');
        \Log::info('Looking for account with ID: ' . $id);
        
        // Find the account with relationships
        $account = BankAccount::with(['creator', 'transactions' => function($query) {
            $query->latest()->limit(20);
        }])->find($id);
        
        // Check if account exists
        if (!$account) {
            \Log::error('Account not found with ID: ' . $id);
            
            // Get all available IDs for debugging
            $availableIds = BankAccount::pluck('id')->toArray();
            \Log::info('Available IDs: ' . implode(', ', $availableIds));
            
            return redirect()->route('finance.bank.accounts.index')
                ->with('error', 'Bank account not found with ID: ' . $id);
        }
        
        \Log::info('Account found: ' . $account->account_name . ' (ID: ' . $account->id . ')');
        
        // Calculate statistics
        $stats = [
            'total_deposits' => \App\Models\BankTransaction::where('bank_account_id', $id)
                ->where('transaction_type', 'deposit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_withdrawals' => \App\Models\BankTransaction::where('bank_account_id', $id)
                ->whereIn('transaction_type', ['withdrawal'])
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_count' => \App\Models\BankTransaction::where('bank_account_id', $id)
                ->where('status', 'pending')
                ->count(),
            'this_month' => \App\Models\BankTransaction::where('bank_account_id', $id)
                ->where('status', 'completed')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount')
        ];
        
        \Log::info('Statistics calculated:', $stats);
        \Log::info('===== END BANK ACCOUNT SHOW METHOD =====');
        
        return view('finance.bank.accounts.show', compact('account', 'stats'));
        
    } catch (\Exception $e) {
        \Log::error('Exception in BankAccountController@show: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return redirect()->route('finance.bank.accounts.index')
            ->with('error', 'Error loading bank account: ' . $e->getMessage());
    }
}

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $account = BankAccount::findOrFail($id);
        $currencies = ['TZS', 'USD', 'KES', 'EUR', 'GBP'];

        return view('finance.bank.accounts.edit', compact('account', 'currencies'));
    }

    /**
     * Update bank account
     */
    public function update(Request $request, $id)
    {
        $account = BankAccount::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number,' . $id,
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // If this is default, remove other defaults
            if ($request->boolean('is_default')) {
                BankAccount::where('is_default', true)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $account->update([
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'branch' => $request->branch,
                'swift_code' => $request->swift_code,
                'currency' => $request->currency,
                'is_active' => $request->boolean('is_active', true),
                'is_default' => $request->boolean('is_default', false),
                'description' => $request->description
            ]);

            DB::commit();

            return redirect()->route('finance.bank.accounts.show', $id)
                ->with('success', 'Bank account updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bank account update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update bank account')
                ->withInput();
        }
    }

    /**
     * Toggle account status
     */
    public function toggleStatus($id)
    {
        try {
            $account = BankAccount::findOrFail($id);
            $account->is_active = !$account->is_active;
            $account->save();

            $status = $account->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Bank account {$status} successfully");

        } catch (\Exception $e) {
            Log::error('Bank account status toggle failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to change account status');
        }
    }

    /**
     * Get account statement
     */
    public function statement($id, Request $request)
    {
        $account = BankAccount::findOrFail($id);

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $transactions = BankTransaction::where('bank_account_id', $id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // Calculate opening balance
        $openingBalance = BankTransaction::where('bank_account_id', $id)
            ->where('transaction_date', '<', $startDate)
            ->where('status', 'completed')
            ->sum(DB::raw("CASE WHEN transaction_type IN ('deposit', 'opening_balance') THEN amount ELSE -amount END"));

        $runningBalance = $openingBalance;

        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'deposit' || $transaction->transaction_type === 'opening_balance') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
        }

        return view('finance.bank.accounts.statement', compact(
            'account', 'transactions', 'openingBalance', 'startDate', 'endDate'
        ));
    }

    /**
     * Generate transaction number
     */
    private function generateTransactionNumber($prefix = 'TXN')
    {
        $year = date('Y');
        $month = date('m');
        
        $lastTxn = BankTransaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTxn) {
            $lastNumber = intval(substr($lastTxn->transaction_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }
}