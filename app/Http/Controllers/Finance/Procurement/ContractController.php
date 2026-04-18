<?php

namespace App\Http\Controllers\Finance\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractDeliverable;
use App\Models\Supplier;
use App\Models\Tender;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['supplier', 'projectManager']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contract_number', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->paginate(15);
        $suppliers = Supplier::all();
        
        $stats = [
            'total' => Contract::count(),
            'active' => Contract::where('status', 'active')->count(),
            'value' => Contract::where('status', 'active')->sum('contract_value'),
            'expiring' => Contract::where('end_date', '<=', now()->addDays(30))
                ->where('status', 'active')
                ->count(),
        ];

        return view('finance.procurement.contracts.index', compact('contracts', 'suppliers', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $tenders = Tender::where('status', 'awarded')->get();
        $requisitions = Requisition::where('status', 'approved')
            ->whereDoesntHave('contract')
            ->get();
        $users = User::where('user_type', 'staff')->get();
        $nextNumber = $this->generateContractNumber();

        return view('finance.procurement.contracts.create', compact('suppliers', 'tenders', 'requisitions', 'users', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'tender_id' => 'nullable|exists:tenders,id',
            'requisition_id' => 'nullable|exists:requisitions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'payment_terms' => 'nullable|string',
            'delivery_terms' => 'nullable|string',
            'project_manager' => 'nullable|exists:users,id',
            'terms' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $contract = Contract::create([
                'contract_number' => $this->generateContractNumber(),
                'title' => $request->title,
                'supplier_id' => $request->supplier_id,
                'tender_id' => $request->tender_id,
                'requisition_id' => $request->requisition_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_value' => $request->contract_value,
                'payment_terms' => $request->payment_terms,
                'delivery_terms' => $request->delivery_terms,
                'project_manager' => $request->project_manager,
                'terms' => $request->terms,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.procurement.contracts.show', $contract->id)
                ->with('success', 'Contract created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create contract: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $contract = Contract::with([
            'supplier', 'tender', 'requisition', 'projectManager',
            'creator', 'approver', 'deliverables'
        ])->findOrFail($id);

        return view('finance.procurement.contracts.show', compact('contract'));
    }

    public function activate($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft contracts can be activated');
        }

        $contract->update(['status' => 'active']);

        return redirect()->route('finance.procurement.contracts.show', $id)
            ->with('success', 'Contract activated successfully');
    }

    public function complete($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'active') {
            return redirect()->back()->with('error', 'Only active contracts can be completed');
        }

        $contract->update(['status' => 'completed']);

        return redirect()->route('finance.procurement.contracts.show', $id)
            ->with('success', 'Contract marked as completed');
    }

    public function addDeliverable(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'due_date' => 'required|date',
            'value' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $contract = Contract::findOrFail($id);

        ContractDeliverable::create([
            'contract_id' => $contract->id,
            'name' => $request->name,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'value' => $request->value
        ]);

        return redirect()->back()->with('success', 'Deliverable added successfully');
    }

    private function generateContractNumber()
    {
        $year = date('Y');
        $last = Contract::whereYear('created_at', $year)->count();
        
        return 'CTR-' . $year . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }
}