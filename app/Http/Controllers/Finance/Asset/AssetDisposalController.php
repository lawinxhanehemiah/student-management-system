<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetDisposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AssetDisposalController extends Controller
{
    /**
     * Display a listing of disposals
     */
    public function index(Request $request)
    {
        $query = AssetDisposal::with(['asset.category']);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('disposal_method')) {
            $query->where('disposal_method', $request->disposal_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('disposal_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('disposal_date', '<=', $request->date_to);
        }

        $disposals = $query->orderBy('disposal_date', 'desc')->paginate(20);

        $assets = Asset::where('status', '!=', 'disposed')->orderBy('name')->get();

        return view('finance.asset.disposals.index', compact('disposals', 'assets'));
    }

    /**
     * Show form for creating new disposal
     */
    public function create(Request $request)
    {
        $assetId = $request->get('asset_id');
        $asset = null;

        if ($assetId) {
            $asset = Asset::with('category')->findOrFail($assetId);
            
            if ($asset->isDisposed()) {
                return redirect()->route('finance.asset.disposals.index')
                    ->with('error', 'Asset is already disposed');
            }
        }

        $assets = Asset::where('status', '!=', 'disposed')
            ->orderBy('name')
            ->get();

        return view('finance.asset.disposals.create', compact('assets', 'asset'));
    }

    /**
     * Store a newly created disposal
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'disposal_date' => 'required|date',
            'disposal_method' => 'required|in:sold,scrapped,donated,lost,stolen,other',
            'disposal_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
            'authorized_by' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $asset = Asset::findOrFail($request->asset_id);

            if ($asset->isDisposed()) {
                throw new \Exception('Asset is already disposed');
            }

            // Calculate gain/loss
            $gainLoss = ($request->disposal_amount ?? 0) - $asset->current_value;

            // Create disposal record
            $disposal = AssetDisposal::create([
                'asset_id' => $asset->id,
                'disposal_date' => $request->disposal_date,
                'disposal_method' => $request->disposal_method,
                'disposal_amount' => $request->disposal_amount,
                'book_value_at_disposal' => $asset->current_value,
                'gain_loss' => $gainLoss,
                'reason' => $request->reason,
                'authorized_by' => $request->authorized_by,
                'created_by' => Auth::id()
            ]);

            // Update asset status
            $asset->status = 'disposed';
            $asset->save();

            DB::commit();

            return redirect()->route('finance.asset.disposals.show', $disposal->id)
                ->with('success', 'Asset disposed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to dispose asset: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified disposal
     */
    public function show($id)
    {
        $disposal = AssetDisposal::with(['asset.category', 'creator'])->findOrFail($id);
        return view('finance.asset.disposals.show', compact('disposal'));
    }

    /**
     * Remove the specified disposal (reverse)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $disposal = AssetDisposal::with('asset')->findOrFail($id);
            $asset = $disposal->asset;

            // Restore asset status
            $asset->status = 'active';
            $asset->save();

            // Delete disposal record
            $disposal->delete();

            DB::commit();

            return redirect()->route('finance.asset.disposals.index')
                ->with('success', 'Disposal reversed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reverse disposal');
        }
    }
}