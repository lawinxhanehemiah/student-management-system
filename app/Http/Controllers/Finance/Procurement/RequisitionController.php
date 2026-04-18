<?php

namespace App\Http\Controllers\Finance\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Department;
use App\Models\ApprovalLevel;
use App\Models\RequisitionApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        $query = Requisition::with(['requester', 'department']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('requisition_number', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        $requisitions = $query->orderBy('created_at', 'desc')->paginate(15);
        $departments = Department::active()->get();
        $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $stats = [
            'total' => Requisition::count(),
            'pending' => Requisition::whereIn('status', ['submitted', 'under_review'])->count(),
            'approved' => Requisition::where('status', 'approved')->count(),
            'total_value' => Requisition::where('status', 'approved')->sum('estimated_total'),
        ];

        return view('finance.procurement.requisitions.index', compact('requisitions', 'departments', 'statuses', 'priorities', 'stats'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $nextNumber = $this->generateRequisitionNumber();
        
        return view('finance.procurement.requisitions.create', compact('departments', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'required_date' => 'required|date|after:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'justification' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate total
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['estimated_unit_price'];
            }

            $requisition = Requisition::create([
                'requisition_number' => $this->generateRequisitionNumber(),
                'title' => $request->title,
                'description' => $request->description,
                'requested_by' => Auth::id(),
                'department_id' => $request->department_id,
                'request_date' => now(),
                'required_date' => $request->required_date,
                'priority' => $request->priority,
                'status' => 'draft',
                'estimated_total' => $total,
                'justification' => $request->justification,
                'created_by' => Auth::id()
            ]);

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['estimated_unit_price'];
                
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'],
                    'estimated_total' => $itemTotal
                ]);
            }

            DB::commit();

            return redirect()->route('finance.procurement.requisitions.show', $requisition->id)
                ->with('success', 'Requisition created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create requisition: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $requisition = Requisition::with([
            'requester', 'department', 'items', 'approvals.approvalLevel', 'approvals.approver'
        ])->findOrFail($id);

        return view('finance.procurement.requisitions.show', compact('requisition'));
    }

    public function edit($id)
    {
        $requisition = Requisition::with('items')->findOrFail($id);
        
        if (!in_array($requisition->status, ['draft', 'rejected'])) {
            return redirect()->route('finance.procurement.requisitions.show', $id)
                ->with('error', 'Only draft or rejected requisitions can be edited');
        }

        $departments = Department::active()->get();
        
        return view('finance.procurement.requisitions.edit', compact('requisition', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $requisition = Requisition::findOrFail($id);

        if (!in_array($requisition->status, ['draft', 'rejected'])) {
            return redirect()->back()->with('error', 'Cannot update this requisition');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'required_date' => 'required|date|after:today',
            'priority' => 'required|in:low,medium,high,urgent',
            'justification' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate total
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['estimated_unit_price'];
            }

            $requisition->update([
                'title' => $request->title,
                'description' => $request->description,
                'department_id' => $request->department_id,
                'required_date' => $request->required_date,
                'priority' => $request->priority,
                'estimated_total' => $total,
                'justification' => $request->justification,
                'status' => 'draft'
            ]);

            // Delete old items
            $requisition->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['estimated_unit_price'];
                
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'],
                    'estimated_total' => $itemTotal
                ]);
            }

            DB::commit();

            return redirect()->route('finance.procurement.requisitions.show', $id)
                ->with('success', 'Requisition updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update requisition');
        }
    }

    public function submit($id)
    {
        $requisition = Requisition::findOrFail($id);

        if ($requisition->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft requisitions can be submitted');
        }

        try {
            DB::beginTransaction();

            $requisition->update(['status' => 'submitted']);

            // Create approval records based on amount
            $approvalLevels = ApprovalLevel::active()
                ->forAmount($requisition->estimated_total)
                ->orderBy('level_order')
                ->get();

            foreach ($approvalLevels as $level) {
                RequisitionApproval::create([
                    'requisition_id' => $requisition->id,
                    'approval_level_id' => $level->id,
                    'status' => 'pending'
                ]);
            }

            DB::commit();

            return redirect()->route('finance.procurement.requisitions.show', $id)
                ->with('success', 'Requisition submitted for approval');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit requisition');
        }
    }

    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $approval = RequisitionApproval::findOrFail($id);
            
            if ($approval->status !== 'pending') {
                throw new \Exception('This approval is not pending');
            }

            // Update approval
            $approval->update([
                'status' => 'approved',
                'approver_id' => Auth::id(),
                'comments' => $request->comments,
                'action_date' => now()
            ]);

            $requisition = $approval->requisition;

            // Check if there are more pending approvals
            $pendingCount = $requisition->approvals()->where('status', 'pending')->count();

            if ($pendingCount == 0) {
                $requisition->update(['status' => 'approved']);
            } else {
                $requisition->update(['status' => 'under_review']);
            }

            DB::commit();

            return redirect()->route('finance.procurement.requisitions.show', $requisition->id)
                ->with('success', 'Requisition approved');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $approval = RequisitionApproval::findOrFail($id);
            
            if ($approval->status !== 'pending') {
                throw new \Exception('This approval is not pending');
            }

            $approval->update([
                'status' => 'rejected',
                'approver_id' => Auth::id(),
                'comments' => $request->comments,
                'action_date' => now()
            ]);

            $approval->requisition->update(['status' => 'rejected']);

            DB::commit();

            return redirect()->route('finance.procurement.requisitions.show', $approval->requisition_id)
                ->with('success', 'Requisition rejected');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $requisition = Requisition::findOrFail($id);

        if (!in_array($requisition->status, ['draft', 'submitted', 'under_review'])) {
            return redirect()->back()->with('error', 'Cannot cancel this requisition');
        }

        $requisition->update(['status' => 'cancelled']);

        return redirect()->route('finance.procurement.requisitions.show', $id)
            ->with('success', 'Requisition cancelled');
    }

    private function generateRequisitionNumber()
    {
        $year = date('Y');
        $month = date('m');
        $last = Requisition::whereYear('created_at', $year)->count();
        
        return 'REQ-' . $year . $month . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}