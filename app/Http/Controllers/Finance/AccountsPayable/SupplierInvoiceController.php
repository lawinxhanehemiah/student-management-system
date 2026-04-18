<?php

namespace App\Http\Controllers\Finance\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceivedNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SupplierInvoiceController extends Controller
{
    /**
     * Display a listing of supplier invoices
     */
    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'purchaseOrder']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Overdue filter
        if ($request->filled('overdue')) {
            $query->where('due_date', '<', now())
                  ->where('status', '!=', 'paid')
                  ->where('balance', '>', 0);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(15);

        $suppliers = Supplier::active()->orderBy('name')->get();
        $statuses = ['pending', 'verified', 'approved', 'partial_paid', 'paid', 'overdue', 'cancelled'];

        return view('finance.accounts-payable.invoices.index', compact('invoices', 'suppliers', 'statuses'));
    }

    /**
     * Show form for creating new invoice
     */
    public function create(Request $request)
    {
        $purchaseOrderId = $request->get('purchase_order_id');
        $purchaseOrder = null;
        $grn = null;
        $items = [];

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'items'])
                ->whereIn('status', ['approved', 'completed'])
                ->find($purchaseOrderId);

            if ($purchaseOrder) {
                // Get GRN for this PO if exists
                $grn = GoodsReceivedNote::where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', 'completed')
                    ->first();

                // Prepare items
                foreach ($purchaseOrder->items as $item) {
                    $items[] = [
                        'purchase_order_item_id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->received_quantity ?: $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $item->tax_rate,
                        'total' => $item->total
                    ];
                }
            }
        }

        $purchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('order_date', 'desc')
            ->get();

        $invoiceNumber = $this->generateInvoiceNumber();

        return view('finance.accounts-payable.invoices.create', compact(
            'purchaseOrders', 'purchaseOrder', 'grn', 'items', 'invoiceNumber'
        ));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'goods_received_note_id' => 'nullable|exists:goods_received_notes,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'supplier_invoice_number' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemTax = $itemTotal * ($item['tax_rate'] / 100);

                $subtotal += $itemTotal;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal + $taxAmount;

            // Create invoice
            $invoice = SupplierInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'goods_received_note_id' => $request->goods_received_note_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'supplier_invoice_number' => $request->supplier_invoice_number,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            // Create items
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemTax = $itemTotal * ($item['tax_rate'] / 100);

                SupplierInvoiceItem::create([
                    'supplier_invoice_id' => $invoice->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'total' => $itemTotal + $itemTax
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier invoice created successfully',
                'invoice' => $invoice,
                'redirect_url' => route('finance.accounts-payable.invoices.show', $invoice->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier invoice creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = SupplierInvoice::with([
            'supplier',
            'purchaseOrder',
            'goodsReceivedNote',
            'items',
            'creator',
            'verifier',
            'approver'
        ])->findOrFail($id);

        return view('finance.accounts-payable.invoices.show', compact('invoice'));
    }

    /**
     * Verify invoice
     */
    public function verify($id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);

            if ($invoice->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending invoices can be verified'
                ], 422);
            }

            $invoice->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice verified successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify invoice'
            ], 500);
        }
    }

    /**
     * Approve invoice
     */
    public function approve($id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);

            if ($invoice->status !== 'verified') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only verified invoices can be approved'
                ], 422);
            }

            $invoice->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve invoice'
            ], 500);
        }
    }

    /**
     * Cancel invoice
     */
    public function cancel(Request $request, $id)
    {
        try {
            $invoice = SupplierInvoice::findOrFail($id);

            if (in_array($invoice->status, ['paid', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice cannot be cancelled'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'cancellation_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoice->update([
                'status' => 'cancelled',
                'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') . 
                          "Cancelled: " . $request->cancellation_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Invoice cancellation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice'
            ], 500);
        }
    }

    /**
     * Print invoice
     */
    public function print($id)
    {
        $invoice = SupplierInvoice::with(['supplier', 'items'])->findOrFail($id);
        
        return view('finance.accounts-payable.invoices.print', compact('invoice'));
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = SupplierInvoice::where('invoice_number', 'like', "APINV/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "APINV/{$year}/{$month}/{$sequence}";
    }
}