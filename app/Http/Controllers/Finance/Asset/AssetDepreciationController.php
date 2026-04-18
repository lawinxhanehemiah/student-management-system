<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetDepreciation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssetDepreciationController extends Controller
{
    /**
     * Display a listing of depreciations
     */
    public function index(Request $request)
    {
        $query = AssetDepreciation::with(['asset.category']);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('depreciation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('depreciation_date', '<=', $request->date_to);
        }

        $depreciations = $query->orderBy('depreciation_date', 'desc')
            ->orderBy('asset_id')
            ->orderBy('period_number', 'desc')
            ->paginate(20);

        $assets = Asset::where('status', 'active')->orderBy('name')->get();

        return view('finance.asset.depreciation.index', compact('depreciations', 'assets'));
    }

    /**
     * Show form for running depreciation
     */
    public function create()
    {
        $assets = Asset::where('status', 'active')
            ->whereRaw('current_value > salvage_value')
            ->orderBy('name')
            ->get();

        return view('finance.asset.depreciation.create', compact('assets'));
    }

    /**
     * Run depreciation for selected asset
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'depreciation_date' => 'required|date',
            'method' => 'required|in:straight_line,declining_balance'
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
                throw new \Exception('Cannot depreciate disposed asset');
            }

            if ($asset->current_value <= $asset->salvage_value) {
                throw new \Exception('Asset already fully depreciated');
            }

            // Get last depreciation period
            $lastDepreciation = AssetDepreciation::where('asset_id', $asset->id)
                ->orderBy('period_number', 'desc')
                ->first();

            $nextPeriod = $lastDepreciation ? $lastDepreciation->period_number + 1 : 1;
            $bookValue = $lastDepreciation ? $lastDepreciation->book_value : $asset->purchase_cost;
            $accumulatedDepreciation = $lastDepreciation ? $lastDepreciation->accumulated_depreciation : 0;

            // Calculate period depreciation based on method
            if ($request->method === 'straight_line') {
                $annualDepreciation = $asset->calculateAnnualDepreciation();
                $periodDepreciation = $annualDepreciation;
                $newBookValue = $bookValue - $periodDepreciation;
            } else {
                // Declining balance (simplified - 200% of straight line)
                $rate = 2 / $asset->useful_life_years;
                $periodDepreciation = $bookValue * $rate;
                $newBookValue = $bookValue - $periodDepreciation;
            }

            // Ensure we don't go below salvage value
            if ($newBookValue < $asset->salvage_value) {
                $periodDepreciation = $bookValue - $asset->salvage_value;
                $newBookValue = $asset->salvage_value;
            }

            $newAccumulated = $accumulatedDepreciation + $periodDepreciation;

            // Create depreciation record
            $depreciation = AssetDepreciation::create([
                'asset_id' => $asset->id,
                'depreciation_date' => $request->depreciation_date,
                'period_number' => $nextPeriod,
                'period_depreciation' => $periodDepreciation,
                'accumulated_depreciation' => $newAccumulated,
                'book_value' => $newBookValue,
                'method' => $request->method,
                'created_by' => Auth::id()
            ]);

            // Update asset current value
            $asset->current_value = $newBookValue;
            $asset->save();

            DB::commit();

            return redirect()->route('finance.asset.depreciation.index')
                ->with('success', "Depreciation calculated successfully for {$asset->name}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to calculate depreciation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Run depreciation for all active assets
     */
    public function run(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'depreciation_date' => 'required|date',
            'method' => 'required|in:straight_line,declining_balance'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $assets = Asset::where('status', 'active')
                ->whereRaw('current_value > salvage_value')
                ->get();

            $count = 0;
            foreach ($assets as $asset) {
                $this->calculateDepreciationForAsset($asset, $request->depreciation_date, $request->method);
                $count++;
            }

            DB::commit();

            return redirect()->route('finance.asset.depreciation.index')
                ->with('success', "Depreciation calculated for {$count} assets");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to run depreciation: ' . $e->getMessage());
        }
    }

    /**
     * Calculate depreciation for single asset
     */
    private function calculateDepreciationForAsset($asset, $date, $method)
    {
        if ($asset->current_value <= $asset->salvage_value) {
            return;
        }

        $lastDepreciation = AssetDepreciation::where('asset_id', $asset->id)
            ->orderBy('period_number', 'desc')
            ->first();

        $nextPeriod = $lastDepreciation ? $lastDepreciation->period_number + 1 : 1;
        $bookValue = $lastDepreciation ? $lastDepreciation->book_value : $asset->purchase_cost;
        $accumulatedDepreciation = $lastDepreciation ? $lastDepreciation->accumulated_depreciation : 0;

        if ($method === 'straight_line') {
            $annualDepreciation = $asset->calculateAnnualDepreciation();
            $periodDepreciation = $annualDepreciation;
        } else {
            $rate = 2 / $asset->useful_life_years;
            $periodDepreciation = $bookValue * $rate;
        }

        $newBookValue = $bookValue - $periodDepreciation;
        if ($newBookValue < $asset->salvage_value) {
            $periodDepreciation = $bookValue - $asset->salvage_value;
            $newBookValue = $asset->salvage_value;
        }

        $newAccumulated = $accumulatedDepreciation + $periodDepreciation;

        AssetDepreciation::create([
            'asset_id' => $asset->id,
            'depreciation_date' => $date,
            'period_number' => $nextPeriod,
            'period_depreciation' => $periodDepreciation,
            'accumulated_depreciation' => $newAccumulated,
            'book_value' => $newBookValue,
            'method' => $method,
            'created_by' => Auth::id()
        ]);

        $asset->current_value = $newBookValue;
        $asset->save();
    }

    /**
     * Show depreciation details
     */
    public function show($id)
    {
        $depreciation = AssetDepreciation::with(['asset.category', 'creator'])->findOrFail($id);
        return view('finance.asset.depreciation.show', compact('depreciation'));
    }
}