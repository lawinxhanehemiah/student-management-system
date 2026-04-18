<?php
// app/Services/AccountsReceivableService.php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Student;
use App\Models\CreditNote;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountsReceivableService
{
    /**
     * Update aging categories for all invoices
     */
    public function updateAgingCategories()
    {
        $today = Carbon::today();
        
        // Update current invoices
        Invoice::where('due_date', '>=', $today)
            ->where('balance', '>', 0)
            ->update([
                'aging_category' => 'current',
                'days_overdue' => 0
            ]);
        
        // Update 1-30 days overdue
        Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 1 AND 30', [$today])
            ->where('balance', '>', 0)
            ->update([
                'aging_category' => '1_30_days',
                'days_overdue' => DB::raw('DATEDIFF(?, due_date)', [$today])
            ]);
        
        // Update 31-60 days overdue
        Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 31 AND 60', [$today])
            ->where('balance', '>', 0)
            ->update([
                'aging_category' => '31_60_days',
                'days_overdue' => DB::raw('DATEDIFF(?, due_date)', [$today])
            ]);
        
        // Update 61-90 days overdue
        Invoice::whereRaw('DATEDIFF(?, due_date) BETWEEN 61 AND 90', [$today])
            ->where('balance', '>', 0)
            ->update([
                'aging_category' => '61_90_days',
                'days_overdue' => DB::raw('DATEDIFF(?, due_date)', [$today])
            ]);
        
        // Update 90+ days overdue
        Invoice::whereRaw('DATEDIFF(?, due_date) > 90', [$today])
            ->where('balance', '>', 0)
            ->update([
                'aging_category' => '90_plus_days',
                'days_overdue' => DB::raw('DATEDIFF(?, due_date)', [$today])
            ]);
        
        // Update collection status
        Invoice::where('balance', '>', 0)
            ->where('due_date', '<', $today->copy()->subDays(90))
            ->update(['collection_status' => 'critical']);
        
        Invoice::where('balance', '>', 0)
            ->whereBetween('due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)])
            ->update(['collection_status' => 'follow_up']);
        
        return true;
    }

    /**
     * Calculate aging summary
     */
    public function calculateAgingSummary($asAtDate = null)
    {
        $asAtDate = $asAtDate ? Carbon::parse($asAtDate) : Carbon::today();
        
        $summary = [
            'current' => $this->getBucketTotal($asAtDate, 0, 0),
            '1_30_days' => $this->getBucketTotal($asAtDate, 1, 30),
            '31_60_days' => $this->getBucketTotal($asAtDate, 31, 60),
            '61_90_days' => $this->getBucketTotal($asAtDate, 61, 90),
            '90_plus_days' => $this->getBucketTotal($asAtDate, 91, null)
        ];
        
        $summary['total'] = array_sum($summary);
        
        return $summary;
    }

    /**
     * Get bucket total
     */
    protected function getBucketTotal($asAtDate, $minDays, $maxDays)
    {
        $query = Invoice::where('balance', '>', 0)
            ->where('due_date', '<', $asAtDate);

        if ($maxDays === null) {
            $query->whereRaw('DATEDIFF(?, due_date) >= ?', [$asAtDate, $minDays]);
        } else {
            $query->whereRaw('DATEDIFF(?, due_date) BETWEEN ? AND ?', [$asAtDate, $minDays, $maxDays]);
        }

        return $query->sum('balance');
    }

    /**
     * Generate reminder for overdue invoices
     */
    public function generateReminders($agingCategory = null)
    {
        $query = Invoice::with('student.user')
            ->where('balance', '>', 0)
            ->where('due_date', '<', now());

        if ($agingCategory) {
            $query->where('aging_category', $agingCategory);
        }

        $invoices = $query->get();
        $reminders = [];

        foreach ($invoices as $invoice) {
            $reminders[] = [
                'invoice' => $invoice,
                'student' => $invoice->student,
                'days_overdue' => $invoice->days_overdue,
                'amount' => $invoice->balance,
                'type' => $this->getReminderType($invoice->days_overdue)
            ];
        }

        return $reminders;
    }

    /**
     * Get reminder type based on days overdue
     */
    protected function getReminderType($daysOverdue)
    {
        if ($daysOverdue <= 15) return 'first_reminder';
        if ($daysOverdue <= 30) return 'second_reminder';
        if ($daysOverdue <= 45) return 'third_reminder';
        if ($daysOverdue <= 60) return 'final_notice';
        return 'legal_action';
    }

    /**
     * Calculate provision for doubtful debts
     */
    public function calculateProvision($asAtDate = null)
    {
        $asAtDate = $asAtDate ? Carbon::parse($asAtDate) : Carbon::today();
        
        $provisionRates = [
            'current' => 0.01,      // 1%
            '1_30_days' => 0.05,     // 5%
            '31_60_days' => 0.10,    // 10%
            '61_90_days' => 0.25,    // 25%
            '90_plus_days' => 0.50    // 50%
        ];

        $provision = 0;
        $details = [];

        foreach ($provisionRates as $bucket => $rate) {
            $amount = $this->getBucketTotal($asAtDate, $this->getBucketMin($bucket), $this->getBucketMax($bucket));
            $bucketProvision = $amount * $rate;
            
            $provision += $bucketProvision;
            $details[$bucket] = [
                'amount' => $amount,
                'rate' => $rate * 100,
                'provision' => $bucketProvision
            ];
        }

        return [
            'total_provision' => $provision,
            'details' => $details,
            'as_at' => $asAtDate->format('Y-m-d')
        ];
    }

    /**
     * Get bucket min days
     */
    protected function getBucketMin($bucket)
    {
        $map = [
            'current' => 0,
            '1_30_days' => 1,
            '31_60_days' => 31,
            '61_90_days' => 61,
            '90_plus_days' => 91
        ];
        
        return $map[$bucket] ?? 0;
    }

    /**
     * Get bucket max days
     */
    protected function getBucketMax($bucket)
    {
        $map = [
            'current' => 0,
            '1_30_days' => 30,
            '31_60_days' => 60,
            '61_90_days' => 90,
            '90_plus_days' => null
        ];
        
        return $map[$bucket] ?? null;
    }

    /**
     * Get debtor days
     */
    public function getDebtorDays($asAtDate = null)
    {
        $asAtDate = $asAtDate ? Carbon::parse($asAtDate) : Carbon::today();
        
        // Average daily credit sales (last 3 months)
        $startDate = $asAtDate->copy()->subMonths(3);
        
        $totalSales = Invoice::whereBetween('issue_date', [$startDate, $asAtDate])
            ->sum('total_amount');
        
        $daysInPeriod = $startDate->diffInDays($asAtDate) + 1;
        $avgDailySales = $daysInPeriod > 0 ? $totalSales / $daysInPeriod : 0;
        
        // Total receivables
        $totalReceivables = Invoice::where('balance', '>', 0)->sum('balance');
        
        // Debtor days
        $debtorDays = $avgDailySales > 0 ? $totalReceivables / $avgDailySales : 0;
        
        return round($debtorDays, 1);
    }

    /**
     * Get collection effectiveness index
     */
    public function getCollectionEffectivenessIndex($period = 'month')
    {
        $endDate = Carbon::today();
        $startDate = match($period) {
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->subMonth(),
            'quarter' => $endDate->copy()->subMonths(3),
            'year' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subMonth()
        };

        // Opening receivables
        $openingReceivables = Invoice::where('issue_date', '<', $startDate)
            ->where('balance', '>', 0)
            ->sum('balance');

        // Credit sales during period
        $creditSales = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Collections during period
        $collections = DB::table('payments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');

        // Closing receivables
        $closingReceivables = Invoice::where('balance', '>', 0)->sum('balance');

        // CEI = (Collections) / (Opening Receivables + (Credit Sales - Closing Receivables)) * 100
        $denominator = $openingReceivables + ($creditSales - $closingReceivables);
        
        if ($denominator <= 0) return 0;

        $cei = ($collections / $denominator) * 100;

        return round($cei, 2);
    }
}