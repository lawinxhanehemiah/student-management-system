<?php

namespace App\Http\Controllers\Finance\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('creator');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_code', 'LIKE', "%{$search}%")
                  ->orWhere('account_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $accounts = $query->orderBy('account_code')->paginate(20);

        // Get account types for filter
        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expenses'
        ];

        return view('finance.general-ledger.chart-of-accounts.index', compact('accounts', 'accountTypes'));
    }

    /**
     * Show form for creating new account
     */
    public function create()
    {
        $parentAccounts = ChartOfAccount::where('is_header', true)
            ->orWhere('level', '<', 3)
            ->orderBy('account_code')
            ->get();

        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expenses'
        ];

        $categories = [
            'asset' => [
                'current_asset' => 'Current Assets',
                'fixed_asset' => 'Fixed Assets'
            ],
            'liability' => [
                'current_liability' => 'Current Liabilities',
                'long_term_liability' => 'Long Term Liabilities'
            ],
            'equity' => [
                'owners_equity' => 'Owner\'s Equity'
            ],
            'revenue' => [
                'operating_revenue' => 'Operating Revenue',
                'other_revenue' => 'Other Revenue'
            ],
            'expense' => [
                'operating_expense' => 'Operating Expenses',
                'administrative_expense' => 'Administrative Expenses',
                'selling_expense' => 'Selling Expenses',
                'other_expense' => 'Other Expenses'
            ]
        ];

        return view('finance.general-ledger.chart-of-accounts.create', compact(
            'parentAccounts', 'accountTypes', 'categories'
        ));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'category' => 'nullable|string',
            'parent_code' => 'nullable|exists:chart_of_accounts,account_code',
            'is_header' => 'boolean',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Determine level
            $level = 1;
            if ($request->parent_code) {
                $parent = ChartOfAccount::where('account_code', $request->parent_code)->first();
                $level = $parent->level + 1;
            }

            // Generate account code
            $accountCode = ChartOfAccount::generateCode(
                $request->account_type,
                $request->parent_code
            );

            $account = ChartOfAccount::create([
                'account_code' => $accountCode,
                'account_name' => $request->account_name,
                'account_type' => $request->account_type,
                'category' => $request->category,
                'parent_code' => $request->parent_code,
                'level' => $level,
                'is_header' => $request->boolean('is_header'),
                'is_active' => true,
                'opening_balance' => $request->opening_balance ?? 0,
                'current_balance' => $request->opening_balance ?? 0,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.general-ledger.chart-of-accounts.index')
                ->with('success', "Account created successfully. Code: {$accountCode}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create account: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified account
     */
    public function show($id)
    {
        $account = ChartOfAccount::with(['creator', 'parent', 'children'])->findOrFail($id);

        // Get recent journal entries for this account
        $recentEntries = $account->journalLines()
            ->with('journalEntry')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('finance.general-ledger.chart-of-accounts.show', compact('account', 'recentEntries'));
    }

    /**
     * Show form for editing account
     */
    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        if ($account->journalLines()->exists()) {
            return redirect()->route('finance.general-ledger.chart-of-accounts.show', $id)
                ->with('error', 'Cannot edit account that has transactions');
        }

        $parentAccounts = ChartOfAccount::where('is_header', true)
            ->orWhere('level', '<', 3)
            ->where('account_code', '!=', $account->account_code)
            ->orderBy('account_code')
            ->get();

        $accountTypes = [
            'asset' => 'Assets',
            'liability' => 'Liabilities',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expenses'
        ];

        $categories = [
            'asset' => [
                'current_asset' => 'Current Assets',
                'fixed_asset' => 'Fixed Assets'
            ],
            'liability' => [
                'current_liability' => 'Current Liabilities',
                'long_term_liability' => 'Long Term Liabilities'
            ],
            'equity' => [
                'owners_equity' => 'Owner\'s Equity'
            ],
            'revenue' => [
                'operating_revenue' => 'Operating Revenue',
                'other_revenue' => 'Other Revenue'
            ],
            'expense' => [
                'operating_expense' => 'Operating Expenses',
                'administrative_expense' => 'Administrative Expenses',
                'selling_expense' => 'Selling Expenses',
                'other_expense' => 'Other Expenses'
            ]
        ];

        return view('finance.general-ledger.chart-of-accounts.edit', compact(
            'account', 'parentAccounts', 'accountTypes', 'categories'
        ));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        if ($account->journalLines()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot edit account that has transactions');
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'category' => 'nullable|string',
            'parent_code' => 'nullable|exists:chart_of_accounts,account_code',
            'is_header' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Determine level
            $level = 1;
            if ($request->parent_code) {
                $parent = ChartOfAccount::where('account_code', $request->parent_code)->first();
                $level = $parent->level + 1;
            }

            $account->update([
                'account_name' => $request->account_name,
                'account_type' => $request->account_type,
                'category' => $request->category,
                'parent_code' => $request->parent_code,
                'level' => $level,
                'is_header' => $request->boolean('is_header'),
                'is_active' => $request->boolean('is_active', true),
                'description' => $request->description
            ]);

            DB::commit();

            return redirect()->route('finance.general-ledger.chart-of-accounts.show', $id)
                ->with('success', 'Account updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Account update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update account')
                ->withInput();
        }
    }

    /**
     * Toggle account status
     */
    public function toggleStatus($id)
    {
        try {
            $account = ChartOfAccount::findOrFail($id);
            $account->is_active = !$account->is_active;
            $account->save();

            $status = $account->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Account {$status} successfully");

        } catch (\Exception $e) {
            Log::error('Account status toggle failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to change account status');
        }
    }

    /**
     * Export chart of accounts
     */
    public function export()
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        $filename = 'chart-of-accounts-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($accounts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Code', 'Name', 'Type', 'Category', 'Level', 
                'Header?', 'Status', 'Current Balance'
            ]);

            foreach ($accounts as $acc) {
                fputcsv($file, [
                    $acc->account_code,
                    $acc->account_name,
                    $acc->type_name,
                    $acc->category_name,
                    $acc->level,
                    $acc->is_header ? 'Yes' : 'No',
                    $acc->is_active ? 'Active' : 'Inactive',
                    $acc->current_balance
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}