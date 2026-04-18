<?php

namespace App\Http\Controllers\Finance\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\GRNItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class GoodsReceivedNoteController extends Controller
{
    /**
     * Display a listing of GRNs
     */
    public function index(Request $request)
    {
        $query = GoodsReceivedNote::with(['supplier', 'purchaseOrder', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('grn_number', 'LIKE', "%{$search}%")
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
            $query->whereDate('receipt_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date_to);
        }

        $grns = $query->orderBy('receipt_date', 'desc')->paginate(15);

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('finance.accounts-payable.grn.index', compact('grns', 'suppliers'));
    }

    /**
     * Show form for creating new GRN
     */
    public function create(Request $request)
    {
        $purchaseOrderId = $request->get('purchase_order_id');
        $purchaseOrder = null;
        $poItems = [];

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'items'])
                ->whereIn('status', ['approved', 'ordered', 'partially_received'])
                ->find($purchaseOrderId);

            if ($purchaseOrder) {
                foreach ($purchaseOrder->items as $item) {
                    $receivedQty = GRNItem::whereHas('goodsReceivedNote', function($q) use ($purchaseOrder) {
                            $q->where('purchase_order_id', $purchaseOrder->id)
                              ->where('status', 'completed');
                        })
                        ->where('purchase_order_item_id', $item->id)
                        ->sum('quantity_accepted');

                    $poItems[] = [
                        'id' => $item->id,
                        'description' => $item->description,
                        'ordered_qty' => $item->quantity,
                        'received_qty' => $receivedQty,
                        'balance' => $item->quantity - $receivedQty,
                        'unit' => $item->unit
                    ];
                }
            }
        }

        $purchaseOrders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['approved', 'ordered', 'partially_received'])
            ->orderBy('order_date', 'desc')
            ->get();

        $grnNumber = $this->generateGRNNumber();

        return view('finance.accounts-payable.grn.create', compact(
            'purchaseOrders', 'purchaseOrder', 'poItems', 'grnNumber'
        ));
    }

    /**
     * Store a newly created GRN
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'delivery_note_number' => 'nullable|string|max:100',
            'received_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'required_if:items.*.quantity_rejected,>0|nullable|string',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.expiry_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);

            // Create GRN
            $grn = GoodsReceivedNote::create([
                'grn_number' => $this->generateGRNNumber(),
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'receipt_date' => $request->receipt_date,
                'delivery_note_number' => $request->delivery_note_number,
                'received_by' => $request->received_by,
                'notes' => $request->notes,
                'status' => 'completed',
                'created_by' => Auth::id()
            ]);

            $allReceived = true;

            // Create GRN items
            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);

                GRNItem::create([
                    'goods_received_note_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'quantity_received' => $item['quantity_received'],
                    'quantity_accepted' => $item['quantity_accepted'],
                    'quantity_rejected' => $item['quantity_rejected'] ?? 0,
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null
                ]);

                // Update received quantity in PO item
                $poItem->received_quantity += $item['quantity_accepted'];
                $poItem->save();

                if ($poItem->received_quantity < $poItem->quantity) {
                    $allReceived = false;
                }
            }

            // Update PO status
            if ($allReceived) {
                $purchaseOrder->status = 'completed';
            } else {
                $purchaseOrder->status = 'partially_received';
            }
            $purchaseOrder->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Goods received note created successfully',
                'grn' => $grn,
                'redirect_url' => route('finance.accounts-payable.grn.show', $grn->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create GRN: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified GRN
     */
    public function show($id)
    {
        $grn = GoodsReceivedNote::with([
            'supplier',
            'purchaseOrder',
            'items.purchaseOrderItem',
            'creator'
        ])->findOrFail($id);

        return view('finance.accounts-payable.grn.show', compact('grn'));
    }

    /**
     * Print GRN
     */
    public function print($id)
    {
        $grn = GoodsReceivedNote::with([
            'supplier',
            'purchaseOrder',
            'items.purchaseOrderItem'
        ])->findOrFail($id);

        return view('finance.accounts-payable.grn.print', compact('grn'));
    }

    /**
     * Cancel GRN
     */
    public function cancel(Request $request, $id)
    {
        try {
            $grn = GoodsReceivedNote::findOrFail($id);

            if ($grn->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'GRN is already cancelled'
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

            DB::beginTransaction();

            // Reverse received quantities
            foreach ($grn->items as $item) {
                $poItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                if ($poItem) {
                    $poItem->received_quantity -= $item->quantity_accepted;
                    $poItem->save();
                }
            }

            // Update GRN status
            $grn->status = 'cancelled';
            $grn->notes = ($grn->notes ? $grn->notes . "\n" : '') . 
                         "Cancelled: " . $request->cancellation_reason;
            $grn->save();

            // Update PO status
            $po = $grn->purchaseOrder;
            $totalReceived = GRNItem::whereHas('goodsReceivedNote', function($q) use ($po) {
                    $q->where('purchase_order_id', $po->id)
                      ->where('status', 'completed');
                })
                ->sum('quantity_accepted');

            if ($totalReceived == 0) {
                $po->status = 'approved';
            } elseif ($totalReceived < $po->items->sum('quantity')) {
                $po->status = 'partially_received';
            } else {
                $po->status = 'completed';
            }
            $po->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'GRN cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GRN cancellation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel GRN: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate GRN number
     */
    private function generateGRNNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastGRN = GoodsReceivedNote::where('grn_number', 'like', "GRN/{$year}/{$month}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastGRN) {
            $parts = explode('/', $lastGRN->grn_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "GRN/{$year}/{$month}/{$sequence}";
    }
}