<?php

namespace App\Http\Controllers\Finance\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FiscalYearController extends Controller
{
    /**
     * Display a listing of fiscal years
     */
    public function index(Request $request)
    {
        $query = FiscalYear::with('creator');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fiscalYears = $query->orderBy('start_date', 'desc')->paginate(20);

        return view('finance.general-ledger.fiscal-years.index', compact('fiscalYears'));
    }

    /**
     * Show form for creating new fiscal year
     */
    public function create()
    {
        return view('finance.general-ledger.fiscal-years.create');
    }

    /**
     * Store a newly created fiscal year
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fiscal_years',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for overlapping fiscal years
        $overlapping = FiscalYear::where(function($q) use ($request) {
            $q->whereBetween('start_date', [$request->start_date, $request->end_date])
              ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
              ->orWhere(function($q2) use ($request) {
                  $q2->where('start_date', '<=', $request->start_date)
                     ->where('end_date', '>=', $request->end_date);
              });
        })->exists();

        if ($overlapping) {
            return redirect()->back()
                ->with('error', 'Fiscal year dates overlap with an existing fiscal year')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // If this is the first fiscal year, make it active
            $isFirst = FiscalYear::count() === 0;

            $fiscalYear = FiscalYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'open',
                'is_active' => $isFirst,
                'notes' => $request->notes,
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.general-ledger.fiscal-years.show', $fiscalYear->id)
                ->with('success', 'Fiscal year created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fiscal year creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create fiscal year: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified fiscal year
     */
    public function show($id)
    {
        $fiscalYear = FiscalYear::with('creator')->findOrFail($id);

        // Get statistics
        $stats = [
            'total_journals' => JournalEntry::whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])->count(),
            'posted_journals' => JournalEntry::whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
                ->where('status', 'posted')
                ->count(),
            'total_debit' => JournalEntry::whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
                ->where('status', 'posted')
                ->sum('total_debit'),
            'total_credit' => JournalEntry::whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
                ->where('status', 'posted')
                ->sum('total_credit')
        ];

        // Get monthly summary
        $monthlySummary = DB::table('journal_entries')
            ->whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
            ->where('status', 'posted')
            ->select(
                DB::raw('YEAR(entry_date) as year'),
                DB::raw('MONTH(entry_date) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_debit) as total_debit'),
                DB::raw('SUM(total_credit) as total_credit')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('finance.general-ledger.fiscal-years.show', compact('fiscalYear', 'stats', 'monthlySummary'));
    }

    /**
     * Show form for editing fiscal year
     */
    public function edit($id)
    {
        $fiscalYear = FiscalYear::findOrFail($id);

        // Don't allow editing if fiscal year is closed
        if ($fiscalYear->status === 'closed') {
            return redirect()->route('finance.general-ledger.fiscal-years.show', $id)
                ->with('error', 'Cannot edit a closed fiscal year');
        }

        return view('finance.general-ledger.fiscal-years.edit', compact('fiscalYear'));
    }

    /**
     * Update the specified fiscal year
     */
    public function update(Request $request, $id)
    {
        $fiscalYear = FiscalYear::findOrFail($id);

        if ($fiscalYear->status === 'closed') {
            return redirect()->back()
                ->with('error', 'Cannot edit a closed fiscal year');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fiscal_years,name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for overlapping fiscal years (excluding current)
        $overlapping = FiscalYear::where('id', '!=', $id)
            ->where(function($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                  ->orWhere(function($q2) use ($request) {
                      $q2->where('start_date', '<=', $request->start_date)
                         ->where('end_date', '>=', $request->end_date);
                  });
            })->exists();

        if ($overlapping) {
            return redirect()->back()
                ->with('error', 'Fiscal year dates overlap with an existing fiscal year')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $fiscalYear->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('finance.general-ledger.fiscal-years.show', $id)
                ->with('success', 'Fiscal year updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fiscal year update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update fiscal year')
                ->withInput();
        }
    }

    /**
     * Set fiscal year as active
     */
    public function setActive($id)
    {
        try {
            DB::beginTransaction();

            // Deactivate all fiscal years
            FiscalYear::query()->update(['is_active' => false]);

            // Activate selected fiscal year
            $fiscalYear = FiscalYear::findOrFail($id);
            $fiscalYear->is_active = true;
            $fiscalYear->save();

            DB::commit();

            return redirect()->back()
                ->with('success', "Fiscal year {$fiscalYear->name} is now active");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fiscal year activation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to set fiscal year as active');
        }
    }

    /**
     * Close fiscal year
     */
    public function close($id)
    {
        try {
            DB::beginTransaction();

            $fiscalYear = FiscalYear::findOrFail($id);

            if ($fiscalYear->status === 'closed') {
                throw new \Exception('Fiscal year is already closed');
            }

            // Check if there are any open journal entries
            $openJournals = JournalEntry::whereBetween('entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
                ->where('status', '!=', 'posted')
                ->exists();

            if ($openJournals) {
                throw new \Exception('Cannot close fiscal year. There are unposted journal entries.');
            }

            // Create closing entries (you might want to implement this based on your accounting rules)
            $this->createClosingEntries($fiscalYear);

            // Close the fiscal year
            $fiscalYear->status = 'closed';
            $fiscalYear->save();

            // If this was the active fiscal year, deactivate it
            if ($fiscalYear->is_active) {
                $fiscalYear->is_active = false;
                $fiscalYear->save();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fiscal year closed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fiscal year closing failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to close fiscal year: ' . $e->getMessage());
        }
    }

    /**
     * Create closing entries for fiscal year
     */
    private function createClosingEntries($fiscalYear)
    {
        // This is a simplified version - you'll need to implement based on your accounting standards
        // Typically closing entries transfer balances from revenue/expense accounts to retained earnings
        
        $retainedEarningsAccount = ChartOfAccount::where('account_code', 'LIKE', '3%')
            ->where('account_name', 'LIKE', '%Retained Earnings%')
            ->first();

        if (!$retainedEarningsAccount) {
            Log::warning('Retained earnings account not found for closing entries');
            return;
        }

        // Get total revenue for the year
        $totalRevenue = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'revenue')
            ->whereBetween('je.entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
            ->where('je.status', 'posted')
            ->sum(DB::raw('jel.credit - jel.debit'));

        // Get total expenses for the year
        $totalExpenses = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'jel.journal_entry_id', '=', 'je.id')
            ->join('chart_of_accounts as coa', 'jel.account_id', '=', 'coa.id')
            ->where('coa.account_type', 'expense')
            ->whereBetween('je.entry_date', [$fiscalYear->start_date, $fiscalYear->end_date])
            ->where('je.status', 'posted')
            ->sum(DB::raw('jel.debit - jel.credit'));

        $netIncome = $totalRevenue - $totalExpenses;

        if (abs($netIncome) > 0.01) {
            // Create closing journal entry
            $journalNumber = 'CL-' . $fiscalYear->name . '-' . date('Ymd');
            
            $journalEntry = JournalEntry::create([
                'journal_number' => $journalNumber,
                'entry_date' => $fiscalYear->end_date,
                'description' => 'Closing entry for fiscal year ' . $fiscalYear->name,
                'type' => 'system',
                'status' => 'posted',
                'total_debit' => $netIncome > 0 ? $netIncome : 0,
                'total_credit' => $netIncome < 0 ? abs($netIncome) : 0,
                'is_balanced' => true,
                'created_by' => Auth::id(),
                'posted_by' => Auth::id(),
                'posted_at' => now()
            ]);

            if ($netIncome > 0) {
                // Debit revenue/credit retained earnings
                $journalEntry->lines()->create([
                    'account_id' => $retainedEarningsAccount->id,
                    'description' => 'Net income transfer',
                    'credit' => $netIncome,
                    'debit' => 0
                ]);
            } else {
                // Debit retained earnings/credit expense
                $journalEntry->lines()->create([
                    'account_id' => $retainedEarningsAccount->id,
                    'description' => 'Net loss transfer',
                    'debit' => abs($netIncome),
                    'credit' => 0
                ]);
            }
        }
    }

    /**
     * Reopen fiscal year
     */
    public function reopen($id)
    {
        try {
            DB::beginTransaction();

            $fiscalYear = FiscalYear::findOrFail($id);

            if ($fiscalYear->status !== 'closed') {
                throw new \Exception('Fiscal year is not closed');
            }

            // Check if there are any journal entries after this fiscal year
            $nextFiscalYear = FiscalYear::where('start_date', '>', $fiscalYear->end_date)
                ->orderBy('start_date')
                ->first();

            if ($nextFiscalYear) {
                $hasEntries = JournalEntry::where('entry_date', '>=', $nextFiscalYear->start_date)
                    ->exists();

                if ($hasEntries) {
                    throw new \Exception('Cannot reopen fiscal year. There are transactions in later periods.');
                }
            }

            $fiscalYear->status = 'open';
            $fiscalYear->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fiscal year reopened successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fiscal year reopening failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to reopen fiscal year: ' . $e->getMessage());
        }
    }
}