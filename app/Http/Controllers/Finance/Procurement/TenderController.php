<?php

namespace App\Http\Controllers\Finance\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderBid;
use App\Models\Supplier;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TenderController extends Controller
{
    public function index(Request $request)
    {
        $query = Tender::with(['requisition']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tender_number', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%");
            });
        }

        $tenders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $stats = [
            'total' => Tender::count(),
            'open' => Tender::where('status', 'published')->where('closing_date', '>=', now())->count(),
            'evaluating' => Tender::where('status', 'evaluating')->count(),
            'awarded' => Tender::where('status', 'awarded')->count(),
        ];

        return view('finance.procurement.tenders.index', compact('tenders', 'stats'));
    }

    public function create()
    {
        $requisitions = Requisition::where('status', 'approved')
            ->whereDoesntHave('tender')
            ->get();
        $nextNumber = $this->generateTenderNumber();
        
        return view('finance.procurement.tenders.create', compact('requisitions', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requisition_id' => 'nullable|exists:requisitions,id',
            'type' => 'required|in:open,closed,restricted,direct',
            'closing_date' => 'required|date|after:today',
            'estimated_value' => 'required|numeric|min:0',
            'terms_and_conditions' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $tender = Tender::create([
                'tender_number' => $this->generateTenderNumber(),
                'title' => $request->title,
                'description' => $request->description,
                'requisition_id' => $request->requisition_id,
                'type' => $request->type,
                'status' => 'draft',
                'closing_date' => $request->closing_date,
                'estimated_value' => $request->estimated_value,
                'terms_and_conditions' => $request->terms_and_conditions,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.procurement.tenders.show', $tender->id)
                ->with('success', 'Tender created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create tender: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $tender = Tender::with(['requisition', 'bids.supplier', 'creator'])->findOrFail($id);
        return view('finance.procurement.tenders.show', compact('tender'));
    }

    public function publish($id)
    {
        $tender = Tender::findOrFail($id);

        if ($tender->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft tenders can be published');
        }

        $tender->update([
            'status' => 'published',
            'published_date' => now()
        ]);

        return redirect()->route('finance.procurement.tenders.show', $id)
            ->with('success', 'Tender published successfully');
    }

    public function closeBidding($id)
    {
        $tender = Tender::findOrFail($id);

        if ($tender->status !== 'published') {
            return redirect()->back()->with('error', 'Only published tenders can be closed');
        }

        $tender->update(['status' => 'evaluating']);

        return redirect()->route('finance.procurement.tenders.show', $id)
            ->with('success', 'Bidding closed. Ready for evaluation');
    }

    private function generateTenderNumber()
    {
        $year = date('Y');
        $month = date('m');
        $last = Tender::whereYear('created_at', $year)->count();
        
        return 'TND-' . $year . $month . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}