<?php
// app/Http/Controllers/Finance/Bank/BankTransactionController.php

namespace App\Http\Controllers\Finance\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BankTransactionController extends Controller
{
    /**
     * Display transactions
     */
    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'creator']);

        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $bankAccounts = BankAccount::where('is_active', true)->get();
        $totals = [
            'deposits' => BankTransaction::where('status', 'completed')
                ->whereIn('transaction_type', ['deposit', 'opening_balance'])
                ->sum('amount'),
            'withdrawals' => BankTransaction::where('status', 'completed')
                ->whereIn('transaction_type', ['withdrawal', 'transfer'])
                ->sum('amount'),
            'pending' => BankTransaction::where('status', 'pending')->count()
        ];

        return view('finance.bank.transactions.index', compact(
            'transactions', 'bankAccounts', 'totals'
        ));
    }

    /**
     * Show deposit form
     */
    public function createDeposit()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();
        return view('finance.bank.transactions.deposit', compact('bankAccounts'));
    }

    /**
     * Store deposit
     */
    public function storeDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
            'payment_id' => 'nullable|exists:payments,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $account = BankAccount::lockForUpdate()->find($request->bank_account_id);
            $balanceBefore = $account->current_balance;

            $transaction = BankTransaction::create([
                'bank_account_id' => $request->bank_account_id,
                'transaction_number' => $this->generateTransactionNumber('DEP'),
                'transaction_date' => $request->transaction_date,
                'transaction_type' => 'deposit',
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $request->amount,
                'reference_type' => $request->payment_id ? Payment::class : null,
                'reference_id' => $request->payment_id,
                'description' => $request->description,
                'status' => 'completed',
                'metadata' => [
                    'reference' => $request->reference,
                    'deposited_by' => Auth::user()->name
                ],
                'created_by' => Auth::id()
            ]);

            // Update account balance
            $account->updateBalance($request->amount, 'credit');

            // If linked to payment, update payment status
            if ($request->payment_id) {
                $payment = Payment::find($request->payment_id);
                $payment->update([
                    'status' => 'completed',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'bank_transaction_id' => $transaction->id,
                        'bank_deposited_at' => now()
                    ])
                ]);
            }

            DB::commit();

            return redirect()->route('finance.bank.transactions.show', $transaction->id)
                ->with('success', 'Deposit recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deposit failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to record deposit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show withdrawal form
     */
    public function createWithdrawal()
    {
        $bankAccounts = BankAccount::where('is_active', true)->get();
        return view('finance.bank.transactions.withdrawal', compact('bankAccounts'));
    }

    /**
     * Store withdrawal
     */
    public function storeWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
            'payee' => 'required|string|max:255',
            'payment_method' => 'required|in:cheque,bank_transfer,cash,withdrawal_slip'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $account = BankAccount::lockForUpdate()->find($request->bank_account_id);

            // Check sufficient balance
            if ($account->current_balance < $request->amount) {
                throw new \Exception('Insufficient balance. Available: ' . number_format($account->current_balance, 2));
            }

            $balanceBefore = $account->current_balance;

            $transaction = BankTransaction::create([
                'bank_account_id' => $request->bank_account_id,
                'transaction_number' => $this->generateTransactionNumber('WDL'),
                'transaction_date' => $request->transaction_date,
                'transaction_type' => 'withdrawal',
                'amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $request->amount,
                'description' => $request->description,
                'status' => 'completed',
                'metadata' => [
                    'reference' => $request->reference,
                    'payee' => $request->payee,
                    'payment_method' => $request->payment_method,
                    'withdrawn_by' => Auth::user()->name
                ],
                'created_by' => Auth::id()
            ]);

            // Update account balance
            $account->updateBalance($request->amount, 'debit');

            DB::commit();

            return redirect()->route('finance.bank.transactions.show', $transaction->id)
                ->with('success', 'Withdrawal recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdrawal failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to record withdrawal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show transaction details
     */
    public function show($id)
    {
        $transaction = BankTransaction::with([
            'bankAccount', 
            'creator', 
            'reference'
        ])->findOrFail($id);

        return view('finance.bank.transactions.show', compact('transaction'));
    }

    /**
     * Transfer between accounts
     */
    public function transfer(Request $request)
    {
        if ($request->isMethod('get')) {
            $bankAccounts = BankAccount::where('is_active', true)->get();
            return view('finance.bank.transactions.transfer', compact('bankAccounts'));
        }

        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:bank_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $fromAccount = BankAccount::lockForUpdate()->find($request->from_account_id);
            $toAccount = BankAccount::lockForUpdate()->find($request->to_account_id);

            // Check sufficient balance
            if ($fromAccount->current_balance < $request->amount) {
                throw new \Exception('Insufficient balance in source account');
            }

            $fromBalanceBefore = $fromAccount->current_balance;
            $toBalanceBefore = $toAccount->current_balance;

            // Create withdrawal from source
            $withdrawal = BankTransaction::create([
                'bank_account_id' => $request->from_account_id,
                'transaction_number' => $this->generateTransactionNumber('TRF'),
                'transaction_date' => $request->transfer_date,
                'transaction_type' => 'transfer',
                'amount' => $request->amount,
                'balance_before' => $fromBalanceBefore,
                'balance_after' => $fromBalanceBefore - $request->amount,
                'description' => 'Transfer to ' . $toAccount->account_name . ': ' . $request->description,
                'status' => 'completed',
                'metadata' => [
                    'reference' => $request->reference,
                    'to_account_id' => $toAccount->id,
                    'to_account_name' => $toAccount->account_name,
                    'transfer_type' => 'outgoing'
                ],
                'created_by' => Auth::id()
            ]);

            // Create deposit to destination
            $deposit = BankTransaction::create([
                'bank_account_id' => $request->to_account_id,
                'transaction_number' => $this->generateTransactionNumber('TRF'),
                'transaction_date' => $request->transfer_date,
                'transaction_type' => 'transfer',
                'amount' => $request->amount,
                'balance_before' => $toBalanceBefore,
                'balance_after' => $toBalanceBefore + $request->amount,
                'description' => 'Transfer from ' . $fromAccount->account_name . ': ' . $request->description,
                'status' => 'completed',
                'metadata' => [
                    'reference' => $request->reference,
                    'from_account_id' => $fromAccount->id,
                    'from_account_name' => $fromAccount->account_name,
                    'transfer_type' => 'incoming'
                ],
                'created_by' => Auth::id()
            ]);

            // Update balances
            $fromAccount->updateBalance($request->amount, 'debit');
            $toAccount->updateBalance($request->amount, 'credit');

            DB::commit();

            return redirect()->route('finance.bank.transactions.show', $withdrawal->id)
                ->with('success', 'Transfer completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to complete transfer: ' . $e->getMessage())
                ->withInput();
        }
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