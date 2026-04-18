<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetTransfer;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AssetTransferController extends Controller
{
    /**
     * Display a listing of transfers
     */
    public function index(Request $request)
    {
        $query = AssetTransfer::with(['asset', 'fromDepartment', 'toDepartment', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')->paginate(20);

        return view('finance.asset.transfers.index', compact('transfers'));
    }

    /**
     * Show pending transfers
     */
    public function pending()
    {
        $transfers = AssetTransfer::with(['asset', 'fromDepartment', 'toDepartment', 'creator'])
            ->where('status', 'pending')
            ->orderBy('transfer_date', 'desc')
            ->paginate(20);

        return view('finance.asset.transfers.pending', compact('transfers'));
    }

    /**
     * Show form for creating new transfer
     */
    public function create(Request $request)
    {
        $assetId = $request->get('asset_id');
        $asset = null;

        if ($assetId) {
            $asset = Asset::findOrFail($assetId);
        }

        $assets = Asset::where('status', 'active')->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();
        $users = User::whereIn('user_type', ['staff'])->orderBy('first_name')->get();

        return view('finance.asset.transfers.create', compact('assets', 'departments', 'users', 'asset'));
    }

    /**
     * Store a newly created transfer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'to_department_id' => 'nullable|exists:departments,id|different:from_department_id',
            'to_user_id' => 'nullable|exists:users,id|different:from_user_id',
            'to_location' => 'nullable|string|max:255',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $asset = Asset::findOrFail($request->asset_id);

            // Create transfer request
            $transfer = AssetTransfer::create([
                'asset_id' => $asset->id,
                'from_department_id' => $asset->department_id,
                'from_user_id' => $asset->assigned_to,
                'from_location' => $asset->location,
                'to_department_id' => $request->to_department_id,
                'to_user_id' => $request->to_user_id,
                'to_location' => $request->to_location,
                'transfer_date' => $request->transfer_date,
                'reason' => $request->reason,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.asset.transfers.show', $transfer->id)
                ->with('success', 'Transfer request created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create transfer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified transfer
     */
    public function show($id)
    {
        $transfer = AssetTransfer::with([
            'asset', 'fromDepartment', 'toDepartment', 
            'fromUser', 'toUser', 'creator', 'approver'
        ])->findOrFail($id);

        return view('finance.asset.transfers.show', compact('transfer'));
    }

    /**
     * Approve transfer
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $transfer = AssetTransfer::with('asset')->findOrFail($id);

            if ($transfer->status !== 'pending') {
                throw new \Exception('Transfer is not pending');
            }

            // Update transfer status
            $transfer->status = 'approved';
            $transfer->approved_by = Auth::id();
            $transfer->approved_at = now();
            $transfer->save();

            // Update asset
            $asset = $transfer->asset;
            $asset->department_id = $transfer->to_department_id ?? $asset->department_id;
            $asset->assigned_to = $transfer->to_user_id ?? $asset->assigned_to;
            $asset->location = $transfer->to_location ?? $asset->location;
            $asset->status = 'transferred';
            $asset->save();

            DB::commit();

            return redirect()->route('finance.asset.transfers.show', $id)
                ->with('success', 'Transfer approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve transfer: ' . $e->getMessage());
        }
    }

    /**
     * Reject transfer
     */
    public function reject($id)
    {
        try {
            $transfer = AssetTransfer::findOrFail($id);

            if ($transfer->status !== 'pending') {
                throw new \Exception('Transfer is not pending');
            }

            $transfer->status = 'rejected';
            $transfer->save();

            return redirect()->route('finance.asset.transfers.show', $id)
                ->with('success', 'Transfer rejected');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reject transfer: ' . $e->getMessage());
        }
    }
}