<?php

namespace App\Http\Controllers\Finance\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of journal entries
     */
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines', 'creator', 'poster']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('journal_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('entry_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('entry_date', '<=', $request->to_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $entries = $query->orderBy('entry_date', 'desc')
                        ->orderBy('journal_number', 'desc')
                        ->paginate(20);

        return view('finance.general-ledger.journal-entries.index', compact('entries'));
    }

    /**
     * Show form for creating new journal entry
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $lastJournalNumber = $this->generateJournalNumber();

        return view('finance.general-ledger.journal-entries.create', compact('accounts', 'lastJournalNumber'));
    }

    /**
     * Store a newly created journal entry
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'type' => 'required|in:manual,system,recurring',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required_without:lines.*.credit|numeric|min:0',
            'lines.*.credit' => 'required_without:lines.*.debit|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalDebit = collect($request->lines)->sum('debit');
            $totalCredit = collect($request->lines)->sum('credit');

            // Check if balanced
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception('Journal entry is not balanced. Debits must equal credits.');
            }

            // Generate journal number
            $journalNumber = $this->generateJournalNumber();

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'journal_number' => $journalNumber,
                'entry_date' => $request->entry_date,
                'description' => $request->description,
                'type' => $request->type,
                'status' => 'draft',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
                'created_by' => Auth::id()
            ]);

            // Create lines
            foreach ($request->lines as $line) {
                $journalEntry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('finance.general-ledger.journal-entries.show', $journalEntry->id)
                ->with('success', "Journal entry {$journalNumber} created successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal entry creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create journal entry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified journal entry
     */
    public function show($id)
    {
        $entry = JournalEntry::with(['lines.account', 'creator', 'poster'])->findOrFail($id);

        return view('finance.general-ledger.journal-entries.show', compact('entry'));
    }

    /**
     * Show form for editing journal entry
     */
    public function edit($id)
    {
        $entry = JournalEntry::with('lines')->findOrFail($id);

        if ($entry->status !== 'draft') {
            return redirect()->route('finance.general-ledger.journal-entries.show', $id)
                ->with('error', 'Only draft entries can be edited');
        }

        $accounts = ChartOfAccount::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        return view('finance.general-ledger.journal-entries.edit', compact('entry', 'accounts'));
    }

    /**
     * Update the specified journal entry
     */
    public function update(Request $request, $id)
    {
        $entry = JournalEntry::findOrFail($id);

        if ($entry->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft entries can be updated');
        }

        $validator = Validator::make($request->all(), [
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required_without:lines.*.credit|numeric|min:0',
            'lines.*.credit' => 'required_without:lines.*.debit|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalDebit = collect($request->lines)->sum('debit');
            $totalCredit = collect($request->lines)->sum('credit');

            // Check if balanced
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception('Journal entry is not balanced. Debits must equal credits.');
            }

            // Update journal entry
            $entry->update([
                'entry_date' => $request->entry_date,
                'description' => $request->description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
            ]);

            // Delete old lines
            $entry->lines()->delete();

            // Create new lines
            foreach ($request->lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('finance.general-ledger.journal-entries.show', $id)
                ->with('success', 'Journal entry updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal entry update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update journal entry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Post journal entry (change status to posted)
     */
    public function post($id)
    {
        try {
            DB::beginTransaction();

            $entry = JournalEntry::with('lines')->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new \Exception('Only draft entries can be posted');
            }

            if (!$entry->is_balanced) {
                throw new \Exception('Cannot post an unbalanced journal entry');
            }

            // Update account balances
            foreach ($entry->lines as $line) {
                $account = ChartOfAccount::find($line->account_id);
                
                // Update current balance based on account type
                $balanceChange = $line->debit - $line->credit;
                
                // For liability, equity, revenue accounts: credit increases balance
                if (in_array($account->account_type, ['liability', 'equity', 'revenue'])) {
                    $balanceChange = -$balanceChange;
                }
                
                $account->current_balance += $balanceChange;
                $account->save();

                // Create or update account balance record for the day
                $this->updateAccountBalance($account, $entry->entry_date, $line);
            }

            // Update entry status
            $entry->status = 'posted';
            $entry->posted_by = Auth::id();
            $entry->posted_at = now();
            $entry->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Journal entry posted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal entry posting failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to post journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Cancel journal entry
     */
    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $entry = JournalEntry::findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new \Exception('Only draft entries can be cancelled');
            }

            $entry->status = 'cancelled';
            $entry->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Journal entry cancelled successfully');

        } catch (\Exception $e) {
            Log::error('Journal entry cancellation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to cancel journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Generate journal number
     */
    private function generateJournalNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastEntry = JournalEntry::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = intval(substr($lastEntry->journal_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "JE-{$year}{$month}-{$newNumber}";
    }

    /**
     * Update account balance record
     */
    private function updateAccountBalance($account, $date, $line)
    {
        $balanceRecord = AccountBalance::firstOrNew([
            'account_id' => $account->id,
            'balance_date' => $date
        ]);

        if (!$balanceRecord->exists) {
            // Get previous day's closing balance
            $prevDayBalance = AccountBalance::where('account_id', $account->id)
                ->where('balance_date', '<', $date)
                ->orderBy('balance_date', 'desc')
                ->first();

            $balanceRecord->opening_balance = $prevDayBalance ? $prevDayBalance->closing_balance : $account->opening_balance;
        }

        $balanceRecord->total_debit += $line->debit;
        $balanceRecord->total_credit += $line->credit;
        $balanceRecord->closing_balance = $balanceRecord->opening_balance + 
            ($account->account_type === 'asset' || $account->account_type === 'expense' 
                ? $balanceRecord->total_debit - $balanceRecord->total_credit
                : $balanceRecord->total_credit - $balanceRecord->total_debit);
        
        $balanceRecord->save();
    }
}