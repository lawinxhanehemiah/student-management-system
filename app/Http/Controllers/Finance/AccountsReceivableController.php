<?php
// app/Http/Controllers/Finance/AccountsReceivableController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Programme;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AccountsReceivableController extends Controller
{
    /**
     * Display aging report - FIXED VERSION
     */
    public function agingReport(Request $request)
    {
        $asAtDate = $request->get('as_at', now()->format('Y-m-d'));
        $asAtDate = Carbon::parse($asAtDate);

        // GET AGING BUCKETS - HAKIKISHA MAJINA YANAFANANA
        $aging = [
            'current' => $this->getAgingBucket($asAtDate, 'current'),
            '1_30_days' => $this->getAgingBucket($asAtDate, '1_30_days'),
            '31_60_days' => $this->getAgingBucket($asAtDate, '31_60_days'),
            '61_90_days' => $this->getAgingBucket($asAtDate, '61_90_days'),
            '90_plus_days' => $this->getAgingBucket($asAtDate, '90_plus_days')
        ];

        // GET AGING BY PROGRAMME
        $programmeBreakdown = $this->getAgingByProgramme($asAtDate);

        // GET AGING BY INVOICE TYPE
        $typeBreakdown = $this->getAgingByInvoiceType($asAtDate);

        // GET LARGEST BALANCES
        $largestBalances = Invoice::with(['student.user', 'student.programme'])
            ->where('balance', '>', 0)
            ->where('due_date', '<', $asAtDate)
            ->orderBy('balance', 'desc')
            ->limit(10)
            ->get();

        // DEBUG - Angalia data kabla ya kutuma
        Log::info('AGING REPORT DATA:', [
            'current' => $aging['current']['amount'],
            '1_30_days' => $aging['1_30_days']['amount'],
            '31_60_days' => $aging['31_60_days']['amount'],
            '61_90_days' => $aging['61_90_days']['amount'],
            '90_plus_days' => $aging['90_plus_days']['amount'],
        ]);

        return view('finance.accounts-receivable.aging', compact(
            'aging',
            'programmeBreakdown',
            'typeBreakdown',
            'largestBalances',
            'asAtDate'
        ));
    }

    /**
     * Get aging bucket - FIXED VERSION
     */
    protected function getAgingBucket($asAtDate, $bucket)
    {
        $query = Invoice::with(['student.user', 'student.programme'])
            ->where('balance', '>', 0);

        switch ($bucket) {
            case 'current':
                // Not overdue (due date >= asAtDate)
                $query->where('due_date', '>=', $asAtDate);
                break;
                
            case '1_30_days':
                // 1-30 days overdue
                $query->where('due_date', '<', $asAtDate)
                      ->whereRaw('DATEDIFF(?, due_date) BETWEEN 1 AND 30', [$asAtDate]);
                break;
                
            case '31_60_days':
                // 31-60 days overdue
                $query->where('due_date', '<', $asAtDate)
                      ->whereRaw('DATEDIFF(?, due_date) BETWEEN 31 AND 60', [$asAtDate]);
                break;
                
            case '61_90_days':
                // 61-90 days overdue
                $query->where('due_date', '<', $asAtDate)
                      ->whereRaw('DATEDIFF(?, due_date) BETWEEN 61 AND 90', [$asAtDate]);
                break;
                
            case '90_plus_days':
                // 90+ days overdue
                $query->where('due_date', '<', $asAtDate)
                      ->whereRaw('DATEDIFF(?, due_date) >= 91', [$asAtDate]);
                break;
                
            default:
                return ['count' => 0, 'amount' => 0, 'invoices' => []];
        }

        return [
            'count' => $query->count(),
            'amount' => $query->sum('balance'),
            'invoices' => $query->limit(20)->get()
        ];
    }

    /**
     * Get aging by programme
     */
    protected function getAgingByProgramme($asAtDate)
    {
        $programmes = Programme::where('is_active', true)->get();
        $result = collect();
        
        foreach ($programmes as $programme) {
            $studentIds = Student::where('programme_id', $programme->id)
                ->where('status', 'active')
                ->pluck('id');
            
            if ($studentIds->isEmpty()) continue;
            
            $invoices = Invoice::whereIn('student_id', $studentIds)
                ->where('balance', '>', 0)
                ->where('due_date', '<', $asAtDate)
                ->get();
            
            if ($invoices->isEmpty()) continue;
            
            $bucket_1_30 = 0;
            $bucket_31_60 = 0;
            $bucket_61_90 = 0;
            $bucket_90_plus = 0;
            $total_balance = 0;
            
            foreach ($invoices as $invoice) {
                $daysOverdue = Carbon::parse($invoice->due_date)->diffInDays($asAtDate, false);
                $total_balance += $invoice->balance;
                
                if ($daysOverdue <= 30) {
                    $bucket_1_30 += $invoice->balance;
                } elseif ($daysOverdue <= 60) {
                    $bucket_31_60 += $invoice->balance;
                } elseif ($daysOverdue <= 90) {
                    $bucket_61_90 += $invoice->balance;
                } else {
                    $bucket_90_plus += $invoice->balance;
                }
            }
            
            $result->push((object)[
                'id' => $programme->id,
                'name' => $programme->name,
                'code' => $programme->code,
                'student_count' => $studentIds->count(),
                'invoice_count' => $invoices->count(),
                'total_balance' => $total_balance,
                'bucket_1_30' => $bucket_1_30,
                'bucket_31_60' => $bucket_31_60,
                'bucket_61_90' => $bucket_61_90,
                'bucket_90_plus' => $bucket_90_plus
            ]);
        }
        
        return $result->sortByDesc('total_balance')->values();
    }

    /**
     * Get aging by invoice type
     */
    protected function getAgingByInvoiceType($asAtDate)
    {
        $types = Invoice::where('balance', '>', 0)
            ->where('due_date', '<', $asAtDate)
            ->select('invoice_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(balance) as total'))
            ->groupBy('invoice_type')
            ->get();
        
        $result = collect();
        
        foreach ($types as $type) {
            $invoices = Invoice::where('invoice_type', $type->invoice_type)
                ->where('balance', '>', 0)
                ->where('due_date', '<', $asAtDate)
                ->get();
            
            $bucket_1_30 = 0;
            $bucket_31_60 = 0;
            $bucket_61_90 = 0;
            $bucket_90_plus = 0;
            
            foreach ($invoices as $invoice) {
                $daysOverdue = Carbon::parse($invoice->due_date)->diffInDays($asAtDate, false);
                
                if ($daysOverdue <= 30) {
                    $bucket_1_30 += $invoice->balance;
                } elseif ($daysOverdue <= 60) {
                    $bucket_31_60 += $invoice->balance;
                } elseif ($daysOverdue <= 90) {
                    $bucket_61_90 += $invoice->balance;
                } else {
                    $bucket_90_plus += $invoice->balance;
                }
            }
            
            $result->push((object)[
                'invoice_type' => $type->invoice_type,
                'count' => $type->count,
                'total_balance' => $type->total,
                'bucket_1_30' => $bucket_1_30,
                'bucket_31_60' => $bucket_31_60,
                'bucket_61_90' => $bucket_61_90,
                'bucket_90_plus' => $bucket_90_plus
            ]);
        }
        
        return $result;
    }

    /**
     * Send payment reminders
     */
    public function sendReminders(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'invoice_ids' => 'required|array',
                'invoice_ids.*' => 'exists:invoices,id',
                'reminder_type' => 'required|in:email,sms,both'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoices = Invoice::with('student.user')
                ->whereIn('id', $request->invoice_ids)
                ->get();
            
            $sent = 0;
            
            foreach ($invoices as $invoice) {
                $invoice->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => $invoice->reminder_count + 1
                ]);
                $sent++;
            }

            return response()->json([
                'success' => true,
                'message' => "Reminders sent to {$sent} invoices"
            ]);

        } catch (\Exception $e) {
            Log::error('Send reminders failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Outstanding invoices
     */
    public function outstanding(Request $request)
    {
        $query = Invoice::with(['student.user', 'student.programme', 'academicYear'])
            ->where('balance', '>', 0)
            ->where('payment_status', '!=', 'paid');

        if ($request->filled('reg_no')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('registration_number', 'LIKE', '%' . $request->reg_no . '%');
            });
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        $invoices = $query->paginate(15);

        $totals = [
            'total_balance' => $query->sum('balance'),
            'total_invoices' => $query->count(),
        ];

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('finance.accounts-receivable.outstanding', compact('invoices', 'totals', 'academicYears'));
    }

    /**
     * Accounts Receivable Dashboard
     */
    public function index()
    {
        $summary = [
            'total_receivable' => Invoice::where('balance', '>', 0)->sum('balance'),
            'total_overdue' => Invoice::where('due_date', '<', now())->where('balance', '>', 0)->sum('balance'),
            'collection_rate' => $this->calculateCollectionRate(),
        ];

        $agingSummary = $this->getAgingSummary();

        $overdueInvoices = Invoice::with(['student.user'])
            ->where('due_date', '<', now())
            ->where('balance', '>', 0)
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        return view('finance.accounts-receivable.index', compact('summary', 'agingSummary', 'overdueInvoices'));
    }

    /**
     * Get aging summary
     */
    protected function getAgingSummary()
    {
        $today = Carbon::today();

        return [
            'current' => [
                'count' => Invoice::where('due_date', '>=', $today)->where('balance', '>', 0)->count(),
                'amount' => Invoice::where('due_date', '>=', $today)->where('balance', '>', 0)->sum('balance')
            ],
            '1_30_days' => [
                'count' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 1 AND 30', [$today])->where('balance', '>', 0)->count(),
                'amount' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 1 AND 30', [$today])->where('balance', '>', 0)->sum('balance')
            ],
            '31_60_days' => [
                'count' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 31 AND 60', [$today])->where('balance', '>', 0)->count(),
                'amount' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 31 AND 60', [$today])->where('balance', '>', 0)->sum('balance')
            ],
            '61_90_days' => [
                'count' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 61 AND 90', [$today])->where('balance', '>', 0)->count(),
                'amount' => Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 61 AND 90', [$today])->where('balance', '>', 0)->sum('balance')
            ],
            '90_plus_days' => [
                'count' => Invoice::whereRaw('DATEDIFF(?, due_date) >= 91', [$today])->where('balance', '>', 0)->count(),
                'amount' => Invoice::whereRaw('DATEDIFF(?, due_date) >= 91', [$today])->where('balance', '>', 0)->sum('balance')
            ]
        ];
    }

    /**
     * Calculate collection rate
     */
    protected function calculateCollectionRate()
    {
        $total = Invoice::sum('total_amount');
        $paid = Invoice::sum('paid_amount');
        return $total > 0 ? round(($paid / $total) * 100, 2) : 0;
    }
}