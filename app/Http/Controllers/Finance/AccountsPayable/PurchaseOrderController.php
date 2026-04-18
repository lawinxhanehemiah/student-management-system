<?php

namespace App\Http\Controllers\Finance\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'LIKE', "%{$search}%")
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
            $query->whereDate('order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->orderBy('order_date', 'desc')->paginate(15);

        // Get filter options
        $suppliers = Supplier::active()->orderBy('name')->get();
        $statuses = ['draft', 'pending_approval', 'approved', 'ordered', 'partially_received', 'completed', 'cancelled'];

        return view('finance.accounts-payable.purchase-orders.index', compact(
            'purchaseOrders', 'suppliers', 'statuses'
        ));
    }

    /**
     * Show form for creating new purchase order
     */
    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $poNumber = $this->generatePONumber();

        return view('finance.accounts-payable.purchase-orders.create', compact('suppliers', 'poNumber'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'required|in:cash,credit_7,credit_15,credit_30,credit_60',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100'
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
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount_rate'] / 100);
                $itemAfterDiscount = $itemTotal - $itemDiscount;
                $itemTax = $itemAfterDiscount * ($item['tax_rate'] / 100);

                $subtotal += $itemTotal;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->generatePONumber(),
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'payment_terms' => $request->payment_terms,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            // Create items
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount_rate'] / 100);
                $itemAfterDiscount = $itemTotal - $itemDiscount;
                $itemTax = $itemAfterDiscount * ($item['tax_rate'] / 100);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $itemDiscount,
                    'total' => $itemTotal - $itemDiscount + $itemTax
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'purchase_order' => $purchaseOrder,
                'redirect_url' => route('finance.accounts-payable.purchase-orders.show', $purchaseOrder->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'items',
            'creator',
            'approver',
            'goodsReceivedNotes'
        ])->findOrFail($id);

        return view('finance.accounts-payable.purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Show form for editing purchase order
     */
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);

        if (!in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
            return redirect()->route('finance.accounts-payable.purchase-orders.show', $id)
                ->with('error', 'Cannot edit purchase order that is already processed');
        }

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('finance.accounts-payable.purchase-orders.edit', compact('purchaseOrder', 'suppliers'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if (!in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit purchase order that is already processed'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'required|in:cash,credit_7,credit_15,credit_30,credit_60',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100'
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
            $discountAmount = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount_rate'] / 100);
                $itemAfterDiscount = $itemTotal - $itemDiscount;
                $itemTax = $itemAfterDiscount * ($item['tax_rate'] / 100);

                $subtotal += $itemTotal;
                $discountAmount += $itemDiscount;
                $taxAmount += $itemTax;
            }

            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'payment_terms' => $request->payment_terms,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'balance' => $totalAmount
            ]);

            // Delete existing items
            $purchaseOrder->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $itemDiscount = $itemTotal * ($item['discount_rate'] / 100);
                $itemAfterDiscount = $itemTotal - $itemDiscount;
                $itemTax = $itemAfterDiscount * ($item['tax_rate'] / 100);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $itemTax,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $itemDiscount,
                    'total' => $itemTotal - $itemDiscount + $itemTax
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'purchase_order' => $purchaseOrder,
                'redirect_url' => route('finance.accounts-payable.purchase-orders.show', $purchaseOrder->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit for approval
     */
    public function submitForApproval($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft purchase orders can be submitted for approval'
                ], 422);
            }

            $purchaseOrder->update([
                'status' => 'pending_approval'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order submitted for approval'
            ]);

        } catch (\Exception $e) {
            Log::error('Submit for approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit purchase order'
            ], 500);
        }
    }

    /**
     * Approve purchase order
     */
    public function approve($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->status !== 'pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending approval purchase orders can be approved'
                ], 422);
            }

            $purchaseOrder->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order approved'
            ]);

        } catch (\Exception $e) {
            Log::error('Approve purchase order failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve purchase order'
            ], 500);
        }
    }

    /**
     * Reject purchase order
     */
    public function reject(Request $request, $id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->status !== 'pending_approval') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending approval purchase orders can be rejected'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $purchaseOrder->update([
                'status' => 'cancelled',
                'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '') . 
                          "Rejected: " . $request->rejection_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order rejected'
            ]);

        } catch (\Exception $e) {
            Log::error('Reject purchase order failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject purchase order'
            ], 500);
        }
    }

    /**
     * Cancel purchase order
     */
    public function cancel(Request $request, $id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (in_array($purchaseOrder->status, ['completed', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order cannot be cancelled'
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

            $purchaseOrder->update([
                'status' => 'cancelled',
                'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '') . 
                          "Cancelled: " . $request->cancellation_reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase order cancelled'
            ]);

        } catch (\Exception $e) {
            Log::error('Cancel purchase order failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel purchase order'
            ], 500);
        }
    }

    /**
     * Print purchase order
     */
    public function print($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items'])->findOrFail($id);
        
        return view('finance.accounts-payable.purchase-orders.print', compact('purchaseOrder'));
    }

    /**
     * Generate PO number
     */
    private function generatePONumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastPO = PurchaseOrder::where('po_number', 'like', "PO/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPO) {
            $parts = explode('/', $lastPO->po_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "PO/{$year}/{$month}/{$sequence}";
    }
}