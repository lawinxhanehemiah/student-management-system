<?php
// app/Http/Controllers/Finance/Bank/CashFlowController.php

namespace App\Http\Controllers\Finance\Bank;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    /**
     * Display cash flow monitoring
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $viewType = $request->get('view', 'chart'); // chart or table

        // Get date range
        $dateRange = $this->getDateRange($period, $request);

        // Get cash flow data
        $cashFlow = $this->getCashFlowData($dateRange['start'], $dateRange['end'], $period);

        // Get projections
        $projections = $this->getCashFlowProjections();

        // Get bank balances
        $bankBalances = BankAccount::where('is_active', true)
            ->select('id', 'bank_name', 'account_name', 'account_number', 'currency', 'current_balance')
            ->get();

        $totalBalance = $bankBalances->sum('current_balance');

        // Get upcoming payments (due in next 30 days)
        $upcomingPayments = Invoice::with(['student.user', 'academicYear'])
            ->where('balance', '>', 0)
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Get recent transactions
        $recentTransactions = BankTransaction::with('bankAccount')
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get();

        return view('finance.bank.cashflow.index', compact(
            'cashFlow',
            'projections',
            'bankBalances',
            'totalBalance',
            'upcomingPayments',
            'recentTransactions',
            'period',
            'viewType'
        ));
    }

    /**
     * Get cash flow data for chart/table
     */
    private function getCashFlowData($startDate, $endDate, $period)
    {
        $data = [];

        if ($period === 'day') {
            // Daily breakdown
            $current = Carbon::parse($startDate);
            while ($current <= Carbon::parse($endDate)) {
                $date = $current->format('Y-m-d');
                $data[] = $this->getDailyCashFlow($date);
                $current->addDay();
            }
        } elseif ($period === 'week') {
            // Weekly breakdown
            $current = Carbon::parse($startDate);
            while ($current <= Carbon::parse($endDate)) {
                $weekStart = $current->copy()->startOfWeek();
                $weekEnd = $current->copy()->endOfWeek();
                $data[] = $this->getWeeklyCashFlow($weekStart, $weekEnd);
                $current->addWeek();
            }
        } elseif ($period === 'month') {
            // Monthly breakdown
            $current = Carbon::parse($startDate);
            while ($current <= Carbon::parse($endDate)) {
                $month = $current->format('Y-m');
                $data[] = $this->getMonthlyCashFlow($month);
                $current->addMonth();
            }
        } elseif ($period === 'quarter') {
            // Quarterly breakdown
            $quarters = [
                'Q1' => ['01-01', '03-31'],
                'Q2' => ['04-01', '06-30'],
                'Q3' => ['07-01', '09-30'],
                'Q4' => ['10-01', '12-31']
            ];

            $year = $startDate->year;
            foreach ($quarters as $quarterName => $dates) {
                $quarterStart = Carbon::parse($year . '-' . $dates[0]);
                $quarterEnd = Carbon::parse($year . '-' . $dates[1]);
                
                if ($quarterStart <= $endDate && $quarterEnd >= $startDate) {
                    $data[] = $this->getQuarterlyCashFlow($quarterStart, $quarterEnd, $quarterName);
                }
            }
        }

        return $data;
    }

    /**
     * Get daily cash flow
     */
    private function getDailyCashFlow($date)
    {
        $inflows = BankTransaction::whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['deposit', 'opening_balance'])
            ->orWhere(function($q) use ($date) {
                $q->whereDate('transaction_date', $date)
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'incoming'");
            })
            ->sum('amount');

        $outflows = BankTransaction::whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['withdrawal'])
            ->orWhere(function($q) use ($date) {
                $q->whereDate('transaction_date', $date)
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'");
            })
            ->sum('amount');

        return [
            'period' => $date,
            'label' => Carbon::parse($date)->format('d M'),
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $inflows - $outflows,
            'inflow_count' => BankTransaction::whereDate('transaction_date', $date)
                ->where('status', 'completed')
                ->whereIn('transaction_type', ['deposit', 'opening_balance'])
                ->count(),
            'outflow_count' => BankTransaction::whereDate('transaction_date', $date)
                ->where('status', 'completed')
                ->whereIn('transaction_type', ['withdrawal'])
                ->count()
        ];
    }

    /**
     * Get weekly cash flow
     */
    private function getWeeklyCashFlow($weekStart, $weekEnd)
    {
        $inflows = BankTransaction::whereBetween('transaction_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['deposit', 'opening_balance'])
            ->orWhere(function($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('transaction_date', [$weekStart, $weekEnd])
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'incoming'");
            })
            ->sum('amount');

        $outflows = BankTransaction::whereBetween('transaction_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['withdrawal'])
            ->orWhere(function($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('transaction_date', [$weekStart, $weekEnd])
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'");
            })
            ->sum('amount');

        return [
            'period' => $weekStart->format('Y-m-d') . ' - ' . $weekEnd->format('Y-m-d'),
            'label' => 'Week ' . $weekStart->weekOfYear . ': ' . $weekStart->format('d M'),
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $inflows - $outflows,
        ];
    }

    /**
     * Get monthly cash flow
     */
    private function getMonthlyCashFlow($month)
    {
        $inflows = BankTransaction::whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['deposit', 'opening_balance'])
            ->orWhere(function($q) use ($month) {
                $q->whereYear('transaction_date', substr($month, 0, 4))
                  ->whereMonth('transaction_date', substr($month, 5, 2))
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'incoming'");
            })
            ->sum('amount');

        $outflows = BankTransaction::whereYear('transaction_date', substr($month, 0, 4))
            ->whereMonth('transaction_date', substr($month, 5, 2))
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['withdrawal'])
            ->orWhere(function($q) use ($month) {
                $q->whereYear('transaction_date', substr($month, 0, 4))
                  ->whereMonth('transaction_date', substr($month, 5, 2))
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'");
            })
            ->sum('amount');

        $paymentInflows = Payment::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))
            ->where('status', 'completed')
            ->sum('amount');

        return [
            'period' => $month,
            'label' => Carbon::parse($month . '-01')->format('M Y'),
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $inflows - $outflows,
            'payment_inflows' => $paymentInflows,
            'other_inflows' => $inflows - $paymentInflows,
        ];
    }

    /**
     * Get quarterly cash flow
     */
    private function getQuarterlyCashFlow($quarterStart, $quarterEnd, $quarterName)
    {
        $inflows = BankTransaction::whereBetween('transaction_date', [$quarterStart, $quarterEnd])
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['deposit', 'opening_balance'])
            ->orWhere(function($q) use ($quarterStart, $quarterEnd) {
                $q->whereBetween('transaction_date', [$quarterStart, $quarterEnd])
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'incoming'");
            })
            ->sum('amount');

        $outflows = BankTransaction::whereBetween('transaction_date', [$quarterStart, $quarterEnd])
            ->where('status', 'completed')
            ->whereIn('transaction_type', ['withdrawal'])
            ->orWhere(function($q) use ($quarterStart, $quarterEnd) {
                $q->whereBetween('transaction_date', [$quarterStart, $quarterEnd])
                  ->where('status', 'completed')
                  ->where('transaction_type', 'transfer')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_type') = 'outgoing'");
            })
            ->sum('amount');

        return [
            'period' => $quarterName . ' ' . $quarterStart->year,
            'label' => $quarterName . ' ' . $quarterStart->year,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $inflows - $outflows,
        ];
    }

    /**
     * Get cash flow projections
     */
    private function getCashFlowProjections()
    {
        // Expected inflows from pending payments
        $expectedInflows = Payment::whereIn('status', ['pending', 'pending_verification'])
            ->sum('amount');

        // Expected outflows from pending supplier invoices
        $expectedOutflows = DB::table('supplier_invoices')
            ->where('status', 'approved')
            ->where('payment_status', '!=', 'paid')
            ->sum('total_amount');

        // Upcoming fee collections
        $upcomingFees = Invoice::where('balance', '>', 0)
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->sum('balance');

        return [
            'expected_inflows' => $expectedInflows,
            'expected_outflows' => $expectedOutflows,
            'upcoming_fees' => $upcomingFees,
            'net_projection' => $expectedInflows - $expectedOutflows,
            'buffer_days' => $this->calculateBufferDays()
        ];
    }

    /**
     * Calculate buffer days (how many days of expenses can be covered)
     */
    private function calculateBufferDays()
    {
        $totalBalance = BankAccount::where('is_active', true)->sum('current_balance');

        // Average daily outflow (last 30 days)
        $avgOutflow = BankTransaction::where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->where('transaction_date', '>=', now()->subDays(30))
            ->avg('amount') ?: 1;

        return $avgOutflow > 0 ? floor($totalBalance / $avgOutflow) : 0;
    }

    /**
     * Get date range based on period
     */
    private function getDateRange($period, $request)
    {
        $year = $request->get('year', now()->year);

        switch ($period) {
            case 'day':
                $date = $request->get('date', now()->format('Y-m-d'));
                return [
                    'start' => Carbon::parse($date)->startOfDay(),
                    'end' => Carbon::parse($date)->endOfDay()
                ];

            case 'week':
                $week = $request->get('week', now()->weekOfYear);
                return [
                    'start' => Carbon::now()->setISODate($year, $week)->startOfWeek(),
                    'end' => Carbon::now()->setISODate($year, $week)->endOfWeek()
                ];

            case 'month':
                $month = $request->get('month', now()->month);
                return [
                    'start' => Carbon::create($year, $month, 1)->startOfMonth(),
                    'end' => Carbon::create($year, $month, 1)->endOfMonth()
                ];

            case 'quarter':
                $quarter = $request->get('quarter', ceil(now()->month / 3));
                $startMonth = ($quarter - 1) * 3 + 1;
                return [
                    'start' => Carbon::create($year, $startMonth, 1)->startOfMonth(),
                    'end' => Carbon::create($year, $startMonth + 2, 1)->endOfMonth()
                ];

            case 'year':
            default:
                return [
                    'start' => Carbon::create($year, 1, 1)->startOfYear(),
                    'end' => Carbon::create($year, 12, 31)->endOfYear()
                ];
        }
    }

    /**
     * Get cash flow data for API (AJAX)
     */
    public function getData(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period, $request);
        $data = $this->getCashFlowData($dateRange['start'], $dateRange['end'], $period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}