<?php
// app/Http/Controllers/Finance/CreditNoteController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of credit notes
     */
    public function index()
    {
        $creditNotes = CreditNote::with(['student.user', 'invoice'])
            ->latest()
            ->paginate(15);
            
        return view('finance.accounts-receivable.credit-notes.index', compact('creditNotes'));
    }

    /**
     * Show the form for creating a new credit note
     */
    public function create()
    {
        $invoices = Invoice::with(['student.user'])
            ->where('balance', '>', 0)
            ->orWhere('paid_amount', '>', 0)
            ->latest()
            ->limit(100)
            ->get();

        return view('finance.accounts-receivable.credit-notes.create', compact('invoices'));
    }

    /**
     * Store a newly created credit note
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|exists:invoices,id',
                'reason' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'expiry_date' => 'nullable|date|after:today',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Find the invoice
            $invoice = Invoice::with('student')->findOrFail($request->invoice_id);

            // Check if amount is valid
            $maxAmount = $invoice->paid_amount + $invoice->balance;
            if ($request->amount > $maxAmount) {
                throw new \Exception("Amount cannot exceed invoice balance of " . number_format($maxAmount, 2));
            }

            // Generate credit note number
            $creditNoteNumber = $this->generateCreditNoteNumber();

            // Create credit note
            $creditNote = CreditNote::create([
                'credit_note_number' => $creditNoteNumber,
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'academic_year_id' => $invoice->academic_year_id,
                'amount' => $request->amount,
                'remaining_amount' => $request->amount,
                'reason' => $request->reason,
                'description' => $request->description,
                'status' => 'active',
                'issue_date' => now(),
                'expiry_date' => $request->expiry_date ?? now()->addYear(),
                'created_by' => Auth::id(),
                'metadata' => json_encode([
                    'source_invoice' => $invoice->invoice_number,
                    'created_at' => now()->toIso8601String()
                ])
            ]);

            // Update invoice metadata
            $metadata = json_decode($invoice->metadata ?? '{}', true);
            if (!isset($metadata['credit_notes'])) {
                $metadata['credit_notes'] = [];
            }
            $metadata['credit_notes'][] = [
                'id' => $creditNote->id,
                'number' => $creditNoteNumber,
                'amount' => $request->amount,
                'date' => now()->toDateTimeString(),
                'reason' => $request->reason
            ];
            $invoice->metadata = json_encode($metadata);
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credit note created successfully',
                'credit_note' => $creditNote,
                'print_url' => route('finance.credit-notes.print', $creditNote->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Credit note creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create credit note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified credit note - 🔥 HIYO NDIO ILIKOSA!
     */
    public function show($id)
    {
        try {
            $creditNote = CreditNote::with([
                'student.user', 
                'student.programme',
                'invoice', 
                'academicYear',
                'createdBy',
                'applications.targetInvoice'
            ])->findOrFail($id);

            return view('finance.accounts-receivable.credit-notes.show', compact('creditNote'));

        } catch (\Exception $e) {
            Log::error('Error viewing credit note: ' . $e->getMessage());
            return redirect()->route('finance.credit-notes.index')
                             ->with('error', 'Credit note not found');
        }
    }

    /**
     * Print credit note
     */
    public function print($id)
    {
        try {
            $creditNote = CreditNote::with(['student.user', 'invoice'])
                ->findOrFail($id);
            
            return view('finance.accounts-receivable.credit-notes.print', compact('creditNote'));
            
        } catch (\Exception $e) {
            return redirect()->route('finance.credit-notes.index')
                             ->with('error', 'Credit note not found');
        }
    }

    /**
     * Download credit note as PDF
     */
    public function download($id)
    {
        try {
            $creditNote = CreditNote::with(['student.user', 'invoice'])
                ->findOrFail($id);
            
            // PDF generation logic here
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.accounts-receivable.credit-notes.pdf', 
                compact('creditNote'));
            
            $filename = 'credit-note-' . str_replace('/', '-', $creditNote->credit_note_number) . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return redirect()->route('finance.credit-notes.index')
                             ->with('error', 'Failed to download credit note');
        }
    }

    /**
     * Apply credit note to invoice
     */
    public function apply(Request $request, $id)
    {
        // Implementation here
    }

    /**
     * Void credit note
     */
    public function void(Request $request, $id)
    {
        // Implementation here
    }

    /**
     * Generate unique credit note number
     */
    private function generateCreditNoteNumber()
    {
        $prefix = 'CN';
        $year = date('Y');
        $month = date('m');
        
        $lastNote = CreditNote::where('credit_note_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastNote) {
            $parts = explode('/', $lastNote->credit_note_number);
            $lastSeq = (int) end($parts);
            $sequence = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }
        
        return "{$prefix}/{$year}/{$month}/{$sequence}";
    }
}