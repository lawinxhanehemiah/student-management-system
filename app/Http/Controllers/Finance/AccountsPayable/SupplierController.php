<?php

namespace App\Http\Controllers\Finance\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::with('creator');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_code', 'LIKE', "%{$search}%")
                  ->orWhere('tax_number', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $suppliers = $query->orderBy('name')->paginate(15);

        // Get unique cities for filter
        $cities = Supplier::distinct('city')->whereNotNull('city')->pluck('city');

        return view('finance.accounts-payable.suppliers.index', compact('suppliers', 'cities'));
    }

    /**
     * Show form for creating new supplier
     */
    public function create()
    {
        return view('finance.accounts-payable.suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $supplier = Supplier::create([
                'supplier_code' => Supplier::generateCode(),
                'name' => $request->name,
                'tax_number' => $request->tax_number,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country ?? 'Tanzania',
                'payment_terms' => $request->payment_terms,
                'credit_limit' => $request->credit_limit ?? 0,
                'opening_balance' => $request->opening_balance ?? 0,
                'current_balance' => $request->opening_balance ?? 0,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_branch' => $request->bank_branch,
                'notes' => $request->notes,
                'status' => 'active',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.accounts-payable.suppliers.index')
                ->with('success', 'Supplier created successfully. Code: ' . $supplier->supplier_code);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create supplier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified supplier
     */
    public function show($id)
    {
        $supplier = Supplier::with([
            'creator',
            'purchaseOrders' => function($q) {
                $q->latest()->limit(10);
            },
            'invoices' => function($q) {
                $q->latest()->limit(10);
            },
            'paymentVouchers' => function($q) {
                $q->latest()->limit(10);
            }
        ])->findOrFail($id);

        // Calculate statistics
        $stats = [
            'total_purchase_orders' => $supplier->purchaseOrders()->count(),
            'total_invoices' => $supplier->invoices()->count(),
            'total_paid' => $supplier->paymentVouchers()->sum('amount'),
            'outstanding_balance' => $supplier->invoices()->where('balance', '>', 0)->sum('balance'),
            'last_purchase' => $supplier->purchaseOrders()->latest()->first()?->order_date,
            'last_payment' => $supplier->paymentVouchers()->latest()->first()?->payment_date
        ];

        return view('finance.accounts-payable.suppliers.show', compact('supplier', 'stats'));
    }

    /**
     * Show form for editing supplier
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('finance.accounts-payable.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $supplier->update([
                'name' => $request->name,
                'tax_number' => $request->tax_number,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'payment_terms' => $request->payment_terms,
                'credit_limit' => $request->credit_limit ?? 0,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_branch' => $request->bank_branch,
                'notes' => $request->notes,
                'status' => $request->status
            ]);

            DB::commit();

            return redirect()->route('finance.accounts-payable.suppliers.index')
                ->with('success', 'Supplier updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update supplier: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // Check if supplier has transactions
            if ($supplier->purchaseOrders()->exists() || $supplier->invoices()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete supplier with existing transactions. Deactivate instead.');
            }

            DB::beginTransaction();
            $supplier->delete();
            DB::commit();

            return redirect()->route('finance.accounts-payable.suppliers.index')
                ->with('success', 'Supplier deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to delete supplier: ' . $e->getMessage());
        }
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->status = $supplier->status === 'active' ? 'inactive' : 'active';
            $supplier->save();

            $message = $supplier->status === 'active' ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Supplier {$message} successfully");

        } catch (\Exception $e) {
            Log::error('Supplier status toggle failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to change supplier status');
        }
    }

    /**
     * Export suppliers list
     */
    public function export(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->orderBy('name')->get();

        // Generate CSV
        $filename = 'suppliers-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($suppliers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Code', 'Name', 'Tax Number', 'Contact Person', 'Email', 
                'Phone', 'City', 'Country', 'Payment Terms', 'Credit Limit',
                'Current Balance', 'Status'
            ]);

            foreach ($suppliers as $s) {
                fputcsv($file, [
                    $s->supplier_code,
                    $s->name,
                    $s->tax_number,
                    $s->contact_person,
                    $s->email,
                    $s->phone,
                    $s->city,
                    $s->country,
                    $s->payment_terms,
                    $s->credit_limit,
                    $s->current_balance,
                    $s->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get supplier statement
     */
    public function statement($id, Request $request)
    {
        $supplier = Supplier::findOrFail($id);

        $fromDate = $request->get('from_date', now()->subMonths(3)->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));

        // Get all transactions (invoices and payments)
        $invoices = $supplier->invoices()
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->orderBy('invoice_date')
            ->get();

        $payments = $supplier->paymentVouchers()
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date')
            ->get();

        // Combine and sort
        $transactions = collect();

        foreach ($invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->invoice_date,
                'type' => 'INVOICE',
                'reference' => $invoice->invoice_number,
                'description' => "Invoice: {$invoice->supplier_invoice_number}",
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'balance' => 0
            ]);
        }

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'PAYMENT',
                'reference' => $payment->voucher_number,
                'description' => "Payment - {$payment->payment_method}",
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => 0
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        // Calculate running balance
        $runningBalance = 0;
        foreach ($transactions as &$t) {
            $runningBalance += $t['debit'] - $t['credit'];
            $t['balance'] = $runningBalance;
        }

        $openingBalance = $supplier->invoices()
            ->where('invoice_date', '<', $fromDate)
            ->sum('total_amount') - $supplier->paymentVouchers()
            ->where('payment_date', '<', $fromDate)
            ->sum('amount');

        return view('finance.accounts-payable.suppliers.statement', compact(
            'supplier', 'transactions', 'openingBalance', 'fromDate', 'toDate'
        ));
    }
}