<?php
// routes/finance.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Finance\PaymentAdjustmentRequestController;
use App\Http\Controllers\Finance\FeeStructureController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Finance\AccountsReceivableController;
use App\Http\Controllers\Finance\CreditNoteController;
use App\Http\Controllers\Finance\RefundController;
use App\Http\Controllers\Finance\StudentStatementController;
use App\Http\Controllers\Finance\AccountsPayable\SupplierController;
use App\Http\Controllers\Finance\AccountsPayable\PurchaseOrderController;
use App\Http\Controllers\Finance\AccountsPayable\GoodsReceivedNoteController;
use App\Http\Controllers\Finance\AccountsPayable\SupplierInvoiceController;
use App\Http\Controllers\Finance\AccountsPayable\PaymentVoucherController;
use App\Http\Controllers\Finance\Reporting\FinancialReportController;
use App\Http\Controllers\Finance\Budget\BudgetYearController;
use App\Http\Controllers\Finance\Budget\DepartmentBudgetController;
use App\Http\Controllers\Finance\Budget\BudgetItemController;
use App\Http\Controllers\Finance\Budget\BudgetRevisionController;
use App\Http\Controllers\Finance\GeneralLedger\ChartOfAccountController;
use App\Http\Controllers\Finance\GeneralLedger\JournalEntryController;
use App\Http\Controllers\Finance\GeneralLedger\TrialBalanceController;
use App\Http\Controllers\Finance\GeneralLedger\LedgerReportController;
use App\Http\Controllers\Finance\GeneralLedger\FiscalYearController;
use App\Http\Controllers\Finance\Bank\BankAccountController;
use App\Http\Controllers\Finance\Bank\BankTransactionController;
use App\Http\Controllers\Finance\Bank\BankReconciliationController;
use App\Http\Controllers\Finance\Bank\CashbookController;
use App\Http\Controllers\Finance\Bank\CashFlowController;

// =====================
// FINANCE MODULE ROUTES
// =====================
Route::middleware(['auth', 'role:Financial_Controller,Accountant,SuperAdmin'])
    ->prefix('finance')
    ->name('finance.')
    ->group(function () {
        
        // ============ DASHBOARD ============
        Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/live-stats', [FinanceController::class, 'getLiveStats'])->name('live-stats');
        Route::get('/collection-trend', [FinanceController::class, 'getCollectionTrend'])->name('collection-trend');
        Route::get('/export-report', [FinanceController::class, 'exportReport'])->name('export-report');
        
        // ============ INVOICE MANAGEMENT ============
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/get-student', [InvoiceController::class, 'getStudent'])->name('get-student');
            Route::post('/generate', [InvoiceController::class, 'generate'])->name('generate');
            Route::post('/verify-fee', [InvoiceController::class, 'verifyFeeConfiguration'])->name('verify-fee');
            Route::get('/statistics', [InvoiceController::class, 'getStatistics'])->name('statistics');
            Route::get('/recent', [InvoiceController::class, 'getRecentActivity'])->name('recent');
            Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
            Route::get('/{id}/print', [InvoiceController::class, 'print'])->name('print');
            Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
        });
        
        // ============ FEE STRUCTURE MANAGEMENT ============
        Route::prefix('fee-structures')->name('fee-structures.')->group(function () {
            Route::get('/', [FeeStructureController::class, 'index'])->name('index');
            Route::get('/{id}', [FeeStructureController::class, 'show'])->name('show');
            Route::get('/by-programme/{programmeId}', [FeeStructureController::class, 'getByProgramme'])->name('by-programme');
            Route::get('/by-level/{programmeId}/{level}', [FeeStructureController::class, 'getByLevel'])->name('by-level');
            Route::get('/export', [FeeStructureController::class, 'export'])->name('export');
        }); 

        // ============ ALL PAYMENTS ============
        Route::prefix('all-payments')->name('all-payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'allPayments'])->name('index');
            Route::get('/export', [PaymentController::class, 'exportAllPayments'])->name('export');
            Route::get('/statistics', [PaymentController::class, 'paymentStatistics'])->name('statistics');
        });

        // ============ PAYMENTS MANAGEMENT ============
        Route::prefix('payments-management')->name('payments-management.')->group(function () {
            Route::get('/', [PaymentController::class, 'paymentsManagementDashboard'])->name('dashboard');
            Route::get('/level/{level}/semester/{semester}', [PaymentController::class, 'studentsByLevelAndSemester'])->name('students');
            Route::get('/exam-eligibility', [PaymentController::class, 'examEligibility'])->name('exam-eligibility');
            Route::get('/export-eligible', [PaymentController::class, 'exportEligibleStudents'])->name('export-eligible');
            Route::get('/student/{studentId}/statement', [PaymentController::class, 'fullStudentStatement'])->name('student-statement');
            Route::get('/student/{studentId}/print', [PaymentController::class, 'printStudentStatement'])->name('print-statement');
            Route::get('/fee-type/{type}', [PaymentController::class, 'paymentsByFeeType'])->name('fee-type');
            Route::get('/fee-type/{type}/student-summary', [PaymentController::class, 'studentPaymentSummaryByType'])->name('fee-type.student-summary');
            Route::get('/fee-type/{type}/export', [PaymentController::class, 'exportFeeTypePayments'])->name('fee-type.export');
        });

        // ============ PAYMENT FILTER ============
        Route::prefix('payment-filter')->name('payment-filter.')->group(function () {
            Route::get('/', [PaymentController::class, 'paymentFilter'])->name('index');
            Route::get('/export', [PaymentController::class, 'exportPaymentFilter'])->name('export');
        });

        // ============ STUDENT PAYMENT INFO ============
        Route::prefix('student-payment-info')->name('student-payment-info.')->group(function () {
            Route::match(['get', 'post'], '/', [PaymentController::class, 'studentPaymentInfoSearch'])->name('search');
            Route::get('/{studentId}', [PaymentController::class, 'studentPaymentInfo'])->name('show');
            Route::get('/{studentId}/all', [PaymentController::class, 'studentAllTransactions'])->name('all');
            Route::get('/{studentId}/print', [PaymentController::class, 'printStudentPaymentInfo'])->name('print');
            Route::get('/{studentId}/export', [PaymentController::class, 'exportStudentPaymentInfo'])->name('export');
            Route::post('/search', [PaymentController::class, 'studentPaymentInfoSearchPost'])->name('search.post');
        });

   // ============ PAYMENT ADJUSTMENT REQUESTS ============
Route::prefix('payment-adjustments')->name('payment-adjustments.')->group(function () {
    Route::get('/my-requests', [PaymentAdjustmentRequestController::class, 'myRequests'])->name('my-requests');
    Route::get('/students/{student}/create', [PaymentAdjustmentRequestController::class, 'create'])->name('create');
    Route::post('/students/{student}', [PaymentAdjustmentRequestController::class, 'store'])->name('store');
    Route::get('/{id}', [PaymentAdjustmentRequestController::class, 'show'])->name('show');
    
});

        // ============ STUDENT STATEMENTS ============
        Route::prefix('student-statements')->name('student-statements.')->group(function () {
            Route::get('/', [StudentStatementController::class, 'index'])->name('index');
            Route::post('/search', [StudentStatementController::class, 'search'])->name('search');
            Route::get('/{studentId}', [StudentStatementController::class, 'show'])->name('show');
            Route::get('/{studentId}/print', [StudentStatementController::class, 'print'])->name('print');
            Route::get('/{studentId}/download', [StudentStatementController::class, 'download'])->name('download');
            Route::post('/{studentId}/email', [StudentStatementController::class, 'email'])->name('email');
            Route::get('/by-reg/{registrationNumber}', [StudentStatementController::class, 'getByRegNo'])->name('by-reg');
            Route::post('/export-bulk', [StudentStatementController::class, 'exportBulk'])->name('export-bulk');
        });

        // ============ REPEAT MODULE FEES ============
        Route::prefix('repeat-fees')->name('repeat-fees.')->group(function () {
            Route::get('/', [FeeStructureController::class, 'repeatIndex'])->name('index');
            Route::get('/create', [FeeStructureController::class, 'createRepeat'])->name('create');
            Route::post('/', [FeeStructureController::class, 'storeRepeat'])->name('store');
            Route::get('/{id}/edit', [FeeStructureController::class, 'editRepeat'])->name('edit');
            Route::put('/{id}', [FeeStructureController::class, 'updateRepeat'])->name('update');
            Route::delete('/{id}', [FeeStructureController::class, 'destroyRepeat'])->name('destroy');
            Route::post('/{id}/activate', [FeeStructureController::class, 'activateRepeat'])->name('activate');
            Route::post('/{id}/deactivate', [FeeStructureController::class, 'deactivateRepeat'])->name('deactivate');
            Route::get('/by-programme/{programmeId}/{level}', [FeeStructureController::class, 'getRepeatByProgramme'])->name('by-programme');
            Route::post('/{id}/toggle-active', [FeeStructureController::class, 'toggleRepeatActive'])->name('toggle-active');
            Route::get('/export', [FeeStructureController::class, 'exportRepeat'])->name('export');
        });
        
        // ============ SUPPLEMENTARY FEES ============
        Route::prefix('supplementary-fees')->name('supplementary-fees.')->group(function () {
            Route::get('/', [FeeStructureController::class, 'supplementaryIndex'])->name('index');
            Route::get('/create', [FeeStructureController::class, 'createSupplementary'])->name('create');
            Route::post('/', [FeeStructureController::class, 'storeSupplementary'])->name('store');
            Route::get('/{id}/edit', [FeeStructureController::class, 'editSupplementary'])->name('edit');
            Route::put('/{id}', [FeeStructureController::class, 'updateSupplementary'])->name('update');
            Route::delete('/{id}', [FeeStructureController::class, 'destroySupplementary'])->name('destroy');
            Route::post('/{id}/activate', [FeeStructureController::class, 'activateSupplementary'])->name('activate');
            Route::post('/{id}/deactivate', [FeeStructureController::class, 'deactivateSupplementary'])->name('deactivate');
            Route::get('/by-programme/{programmeId}/{level}', [FeeStructureController::class, 'getSupplementaryByProgramme'])->name('by-programme');
            Route::post('/{id}/toggle-active', [FeeStructureController::class, 'toggleSupplementaryActive'])->name('toggle-active');
            Route::get('/export', [FeeStructureController::class, 'exportSupplementary'])->name('export');
        });
        
        // ============ PAYMENT MANAGEMENT ============
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'paymentHistory'])->name('index');
            Route::get('/{id}', [PaymentController::class, 'showPayment'])->name('show');
            Route::post('/{id}/verify', [PaymentController::class, 'verifyManualPayment'])->name('verify');
            Route::get('/{id}/receipt', [PaymentController::class, 'downloadReceipt'])->name('receipt');
            Route::get('/statistics', [PaymentController::class, 'getStatistics'])->name('statistics');
        });
        
        // ============ CONTROL NUMBERS ============
        Route::prefix('control-numbers')->name('control-numbers.')->group(function () {
            Route::post('/generate/{invoiceNumber}', [PaymentController::class, 'generateControlNumber'])->name('generate');
            Route::get('/status/{controlNumber}', [PaymentController::class, 'checkPaymentStatus'])->name('status');
        });
        
        // ============ MOBILE PAYMENTS ============
        Route::prefix('mobile-payments')->name('mobile-payments.')->group(function () {
            Route::post('/initiate/{invoiceNumber}', [PaymentController::class, 'initiateMobilePayment'])->name('initiate');
            Route::post('/initiate-by-id/{id}', [PaymentController::class, 'initiateMPesaPaymentById'])->name('initiate-by-id');
            Route::post('/mpesa/callback', [PaymentController::class, 'handleMPesaCallback'])->name('mpesa-callback');
            Route::post('/nmb/callback', [PaymentController::class, 'handleNMBWebhook'])->name('nmb-callback');
        });
        
        // ============ MANUAL PAYMENTS ============
        Route::prefix('manual-payments')->name('manual-payments.')->group(function () {
            Route::post('/process/{invoiceNumber}', [PaymentController::class, 'processManualPayment'])->name('process');
        });
        
        // ============ REPORTS ============
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [FinanceController::class, 'revenueReport'])->name('revenue');
            Route::get('/revenue/export', [FinanceController::class, 'exportRevenue'])->name('revenue.export');
            Route::get('/outstanding', [FinanceController::class, 'outstandingReport'])->name('outstanding');
            Route::get('/outstanding/export', [FinanceController::class, 'exportOutstanding'])->name('outstanding.export');
            Route::get('/programme/{programmeId}', [FinanceController::class, 'programmeReport'])->name('programme');
            Route::get('/programme/{programmeId}/export', [FinanceController::class, 'exportProgramme'])->name('programme.export');
            Route::get('/student/{studentId}', [FinanceController::class, 'studentReport'])->name('student');
            Route::get('/student/{studentId}/export', [FinanceController::class, 'exportStudent'])->name('student.export');
            Route::get('/daily', [FinanceController::class, 'dailySummary'])->name('daily');
            Route::get('/weekly', [FinanceController::class, 'weeklySummary'])->name('weekly');
            Route::get('/monthly', [FinanceController::class, 'monthlySummary'])->name('monthly');
        });
        
        // ============ OUTSTANDING BALANCES ============
        Route::prefix('outstanding')->name('outstanding.')->group(function () {
            Route::get('/30-days', [FinanceController::class, 'outstanding30Days'])->name('30-days');
            Route::get('/60-days', [FinanceController::class, 'outstanding60Days'])->name('60-days');
            Route::get('/90-days', [FinanceController::class, 'outstanding90Days'])->name('90-days');
            Route::post('/send-reminders', [FinanceController::class, 'sendReminders'])->name('send-reminders');
        });
        
        // ============ COLLECTIONS SUMMARY ============
        Route::prefix('collections')->name('collections.')->group(function () {
            Route::get('/today', [FinanceController::class, 'todayCollections'])->name('today');
            Route::get('/weekly', [FinanceController::class, 'weeklyCollections'])->name('weekly');
            Route::get('/monthly', [FinanceController::class, 'monthlyCollections'])->name('monthly');
            Route::get('/by-programme', [FinanceController::class, 'collectionsByProgramme'])->name('by-programme');
            Route::get('/export', [FinanceController::class, 'exportCollections'])->name('export');
        });
        
        // ============ ACCOUNTS RECEIVABLE ============
        Route::prefix('accounts-receivable')->name('accounts-receivable.')->group(function () {
            Route::get('/', [AccountsReceivableController::class, 'index'])->name('index');
            Route::get('/outstanding', [AccountsReceivableController::class, 'outstanding'])->name('outstanding');
            Route::get('/aging', [AccountsReceivableController::class, 'agingReport'])->name('aging');
            Route::post('/send-reminders', [AccountsReceivableController::class, 'sendReminders'])->name('send-reminders');
            Route::post('/write-off/{invoice}', [AccountsReceivableController::class, 'writeOff'])->name('write-off');
            Route::get('/export-aging', [AccountsReceivableController::class, 'exportAgingReport'])->name('export-aging');
        });
        
        // ============ CREDIT NOTES ============
        Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
            Route::get('/', [CreditNoteController::class, 'index'])->name('index');
            Route::get('/create', [CreditNoteController::class, 'create'])->name('create');
            Route::post('/', [CreditNoteController::class, 'store'])->name('store');
            Route::post('/{creditNote}/apply', [CreditNoteController::class, 'apply'])->name('apply');
            Route::post('/{creditNote}/void', [CreditNoteController::class, 'void'])->name('void');
            Route::get('/{creditNote}/print', [CreditNoteController::class, 'print'])->name('print');
            Route::get('/{creditNote}/download', [CreditNoteController::class, 'download'])->name('download');
            Route::get('/get-invoices', [CreditNoteController::class, 'getInvoicesForApplication'])->name('get-invoices');
            Route::get('/{creditNote}', [CreditNoteController::class, 'show'])->name('show');
        });
        
        // ============ REFUNDS ============
        Route::prefix('refunds')->name('refunds.')->group(function () {
            Route::get('/', [RefundController::class, 'index'])->name('index');
            Route::get('/create', [RefundController::class, 'create'])->name('create');
            Route::post('/', [RefundController::class, 'store'])->name('store');
            Route::post('/{refund}/approve', [RefundController::class, 'approve'])->name('approve');
            Route::post('/{refund}/process', [RefundController::class, 'process'])->name('process');
            Route::post('/{refund}/reject', [RefundController::class, 'reject'])->name('reject');
            Route::get('/{refund}', [RefundController::class, 'show'])->name('show');
        });
        
        // ============ ACCOUNTS PAYABLE ============
        Route::prefix('accounts-payable')->name('accounts-payable.')->group(function () {
            Route::prefix('api')->name('api.')->group(function () {
                Route::get('/suppliers/{supplierId}/invoices', [SupplierInvoiceController::class, 'getSupplierInvoices']);
                Route::get('/purchase-orders/{poId}/grns', [PurchaseOrderController::class, 'getGrns']);
                Route::get('/purchase-orders/{poId}/items', [PurchaseOrderController::class, 'getItems']);
            });
            
            Route::prefix('suppliers')->name('suppliers.')->group(function () {
                Route::get('/', [SupplierController::class, 'index'])->name('index');
                Route::get('/create', [SupplierController::class, 'create'])->name('create');
                Route::post('/', [SupplierController::class, 'store'])->name('store');
                Route::get('/{id}', [SupplierController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
                Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
                Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{id}/statement', [SupplierController::class, 'statement'])->name('statement');
                Route::get('/export', [SupplierController::class, 'export'])->name('export');
            });
            
            Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
                Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
                Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
                Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
                Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('edit');
                Route::put('/{id}', [PurchaseOrderController::class, 'update'])->name('update');
                Route::post('/{id}/submit', [PurchaseOrderController::class, 'submitForApproval'])->name('submit');
                Route::post('/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('reject');
                Route::post('/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('cancel');
                Route::get('/{id}/print', [PurchaseOrderController::class, 'print'])->name('print');
            });
            
            Route::prefix('grn')->name('grn.')->group(function () {
                Route::get('/', [GoodsReceivedNoteController::class, 'index'])->name('index');
                Route::get('/create', [GoodsReceivedNoteController::class, 'create'])->name('create');
                Route::post('/', [GoodsReceivedNoteController::class, 'store'])->name('store');
                Route::get('/{id}', [GoodsReceivedNoteController::class, 'show'])->name('show');
                Route::post('/{id}/cancel', [GoodsReceivedNoteController::class, 'cancel'])->name('cancel');
                Route::get('/{id}/print', [GoodsReceivedNoteController::class, 'print'])->name('print');
            });
            
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::get('/', [SupplierInvoiceController::class, 'index'])->name('index');
                Route::get('/create', [SupplierInvoiceController::class, 'create'])->name('create');
                Route::post('/', [SupplierInvoiceController::class, 'store'])->name('store');
                Route::get('/{id}', [SupplierInvoiceController::class, 'show'])->name('show');
                Route::post('/{id}/verify', [SupplierInvoiceController::class, 'verify'])->name('verify');
                Route::post('/{id}/approve', [SupplierInvoiceController::class, 'approve'])->name('approve');
                Route::post('/{id}/cancel', [SupplierInvoiceController::class, 'cancel'])->name('cancel');
                Route::get('/{id}/print', [SupplierInvoiceController::class, 'print'])->name('print');
            });
            
            Route::prefix('payment-vouchers')->name('payment-vouchers.')->group(function () {
                Route::get('/', [PaymentVoucherController::class, 'index'])->name('index');
                Route::get('/create', [PaymentVoucherController::class, 'create'])->name('create');
                Route::post('/', [PaymentVoucherController::class, 'store'])->name('store');
                Route::get('/{id}', [PaymentVoucherController::class, 'show'])->name('show');
                Route::post('/{id}/submit', [PaymentVoucherController::class, 'submitForApproval'])->name('submit');
                Route::post('/{id}/approve', [PaymentVoucherController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [PaymentVoucherController::class, 'reject'])->name('reject');
                Route::post('/{id}/paid', [PaymentVoucherController::class, 'markAsPaid'])->name('paid');
                Route::get('/{id}/print', [PaymentVoucherController::class, 'print'])->name('print');
            });
        });
        
        // ============ AUDIT & COMPLIANCE ============
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/audit-trail', [App\Http\Controllers\Finance\Audit\AuditController::class, 'auditTrail'])->name('audit-trail');
            Route::get('/audit-trail/{id}', [App\Http\Controllers\Finance\Audit\AuditController::class, 'showAudit'])->name('show-audit');
            Route::get('/transaction-logs', [App\Http\Controllers\Finance\Audit\AuditController::class, 'transactionLogs'])->name('transaction-logs');
            Route::get('/transaction-logs/{id}', [App\Http\Controllers\Finance\Audit\AuditController::class, 'showTransaction'])->name('show-transaction');
            Route::get('/role-activity', [App\Http\Controllers\Finance\Audit\AuditController::class, 'roleActivity'])->name('role-activity');
            Route::get('/export', [App\Http\Controllers\Finance\Audit\AuditController::class, 'export'])->name('export');
        });

        // ============ ASSET MANAGEMENT ============
        Route::prefix('asset')->name('asset.')->group(function () {
            Route::resource('categories', App\Http\Controllers\Finance\Asset\AssetCategoryController::class);
            
            Route::get('assets/export', [App\Http\Controllers\Finance\Asset\AssetController::class, 'export'])->name('assets.export');
            Route::get('assets/statistics', [App\Http\Controllers\Finance\Asset\AssetController::class, 'statistics'])->name('assets.statistics');
            Route::get('assets/{id}/depreciation', [App\Http\Controllers\Finance\Asset\AssetController::class, 'calculateDepreciation'])->name('assets.depreciation.calculate');
            Route::resource('assets', App\Http\Controllers\Finance\Asset\AssetController::class);
            
            Route::get('depreciation/run', [App\Http\Controllers\Finance\Asset\AssetDepreciationController::class, 'run'])->name('depreciation.run');
            Route::resource('depreciation', App\Http\Controllers\Finance\Asset\AssetDepreciationController::class);
            
            Route::resource('disposals', App\Http\Controllers\Finance\Asset\AssetDisposalController::class);
            
            Route::get('transfers/pending', [App\Http\Controllers\Finance\Asset\AssetTransferController::class, 'pending'])->name('transfers.pending');
            Route::post('transfers/{id}/approve', [App\Http\Controllers\Finance\Asset\AssetTransferController::class, 'approve'])->name('transfers.approve');
            Route::post('transfers/{id}/reject', [App\Http\Controllers\Finance\Asset\AssetTransferController::class, 'reject'])->name('transfers.reject');
            Route::resource('transfers', App\Http\Controllers\Finance\Asset\AssetTransferController::class);
        });

        // ============ FINANCIAL REPORTING ============
        Route::prefix('reporting')->name('reporting.')->group(function () {
            Route::get('/income-statement', [FinancialReportController::class, 'incomeStatement'])->name('income-statement');
            Route::get('/income-statement/export', [FinancialReportController::class, 'exportIncomeStatement'])->name('income-statement.export');
            Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/cash-flow', [FinancialReportController::class, 'cashFlowStatement'])->name('cash-flow');
            Route::get('/departments', [FinancialReportController::class, 'departmentReports'])->name('departments');
        });
        
        // ============ BUDGET MANAGEMENT ============
        Route::prefix('budget')->name('budget.')->group(function () {
            Route::prefix('years')->name('years.')->group(function () {
                Route::get('/', [BudgetYearController::class, 'index'])->name('index');
                Route::get('/create', [BudgetYearController::class, 'create'])->name('create');
                Route::post('/', [BudgetYearController::class, 'store'])->name('store');
                Route::get('/{id}', [BudgetYearController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [BudgetYearController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BudgetYearController::class, 'update'])->name('update');
                Route::post('/{id}/submit', [BudgetYearController::class, 'submitForApproval'])->name('submit');
                Route::post('/{id}/approve', [BudgetYearController::class, 'approve'])->name('approve');
                Route::post('/{id}/reject', [BudgetYearController::class, 'reject'])->name('reject');
                Route::post('/{id}/close', [BudgetYearController::class, 'close'])->name('close');
                Route::get('/{id}/vs-actual', [BudgetYearController::class, 'vsActual'])->name('vs-actual');
            });
            
            Route::prefix('departments')->name('departments.')->group(function () {
                Route::get('/{budgetId}', [DepartmentBudgetController::class, 'index'])->name('index');
                Route::get('/{budgetId}/create', [DepartmentBudgetController::class, 'create'])->name('create');
                Route::post('/{budgetId}', [DepartmentBudgetController::class, 'store'])->name('store');
                Route::get('/{budgetId}/{allocationId}', [DepartmentBudgetController::class, 'show'])->name('show');
                Route::put('/{budgetId}/{allocationId}', [DepartmentBudgetController::class, 'update'])->name('update');
                Route::delete('/{budgetId}/{allocationId}', [DepartmentBudgetController::class, 'destroy'])->name('destroy');
            });
            
            Route::prefix('items')->name('items.')->group(function () {
                Route::get('/{budgetId}', [BudgetItemController::class, 'index'])->name('index');
                Route::get('/{budgetId}/create', [BudgetItemController::class, 'create'])->name('create');
                Route::post('/{budgetId}', [BudgetItemController::class, 'store'])->name('store');
                Route::put('/{budgetId}/{itemId}', [BudgetItemController::class, 'update'])->name('update');
                Route::delete('/{budgetId}/{itemId}', [BudgetItemController::class, 'destroy'])->name('destroy');
            });
            
            Route::prefix('revisions')->name('revisions.')->group(function () {
                Route::get('/{budgetId}', [BudgetRevisionController::class, 'index'])->name('index');
                Route::get('/{budgetId}/create', [BudgetRevisionController::class, 'create'])->name('create');
                Route::post('/{budgetId}', [BudgetRevisionController::class, 'store'])->name('store');
                Route::post('/{budgetId}/{revisionId}/approve', [BudgetRevisionController::class, 'approve'])->name('approve');
                Route::post('/{budgetId}/{revisionId}/reject', [BudgetRevisionController::class, 'reject'])->name('reject');
            });
        });

        // ============ GENERAL LEDGER ============
        Route::prefix('general-ledger')->name('general-ledger.')->group(function () {
            Route::prefix('chart-of-accounts')->name('chart-of-accounts.')->group(function () {
                Route::get('/', [ChartOfAccountController::class, 'index'])->name('index');
                Route::get('/create', [ChartOfAccountController::class, 'create'])->name('create');
                Route::post('/', [ChartOfAccountController::class, 'store'])->name('store');
                Route::get('/{id}', [ChartOfAccountController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [ChartOfAccountController::class, 'edit'])->name('edit');
                Route::put('/{id}', [ChartOfAccountController::class, 'update'])->name('update');
                Route::post('/{id}/toggle-status', [ChartOfAccountController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/export/csv', [ChartOfAccountController::class, 'export'])->name('export');
            });
            
            Route::prefix('journal-entries')->name('journal-entries.')->group(function () {
                Route::get('/', [JournalEntryController::class, 'index'])->name('index');
                Route::get('/create', [JournalEntryController::class, 'create'])->name('create');
                Route::post('/', [JournalEntryController::class, 'store'])->name('store');
                Route::get('/{id}', [JournalEntryController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [JournalEntryController::class, 'edit'])->name('edit');
                Route::put('/{id}', [JournalEntryController::class, 'update'])->name('update');
                Route::post('/{id}/post', [JournalEntryController::class, 'post'])->name('post');
                Route::post('/{id}/cancel', [JournalEntryController::class, 'cancel'])->name('cancel');
            });
            
            Route::prefix('trial-balance')->name('trial-balance.')->group(function () {
                Route::get('/', [TrialBalanceController::class, 'index'])->name('index');
                Route::get('/export', [TrialBalanceController::class, 'export'])->name('export');
                Route::get('/print', [TrialBalanceController::class, 'print'])->name('print');
            });
            
            Route::prefix('ledger-reports')->name('ledger-reports.')->group(function () {
                Route::get('/', [LedgerReportController::class, 'index'])->name('index');
                Route::get('/summary', [LedgerReportController::class, 'summary'])->name('summary');
                Route::get('/export', [LedgerReportController::class, 'export'])->name('export');
            });
            
            Route::prefix('fiscal-years')->name('fiscal-years.')->group(function () {
                Route::get('/', [FiscalYearController::class, 'index'])->name('index');
                Route::get('/create', [FiscalYearController::class, 'create'])->name('create');
                Route::post('/', [FiscalYearController::class, 'store'])->name('store');
                Route::get('/{id}', [FiscalYearController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [FiscalYearController::class, 'edit'])->name('edit');
                Route::put('/{id}', [FiscalYearController::class, 'update'])->name('update');
                Route::post('/{id}/set-active', [FiscalYearController::class, 'setActive'])->name('set-active');
                Route::post('/{id}/close', [FiscalYearController::class, 'close'])->name('close');
                Route::post('/{id}/reopen', [FiscalYearController::class, 'reopen'])->name('reopen');
            });
        });

        // ============ PROCUREMENT & WORKFLOW ============
Route::prefix('procurement')->name('procurement.')->group(function () {
    
    // Requisitions
    Route::post('requisitions/{id}/submit', [App\Http\Controllers\Finance\Procurement\RequisitionController::class, 'submit'])->name('requisitions.submit');
    Route::post('requisitions/approvals/{id}/approve', [App\Http\Controllers\Finance\Procurement\RequisitionController::class, 'approve'])->name('requisitions.approve');
    Route::post('requisitions/approvals/{id}/reject', [App\Http\Controllers\Finance\Procurement\RequisitionController::class, 'reject'])->name('requisitions.reject');
    Route::post('requisitions/{id}/cancel', [App\Http\Controllers\Finance\Procurement\RequisitionController::class, 'cancel'])->name('requisitions.cancel');
    Route::resource('requisitions', App\Http\Controllers\Finance\Procurement\RequisitionController::class);
    
    // Approval Levels
    Route::post('approval-levels/{id}/toggle-status', [App\Http\Controllers\Finance\Procurement\ApprovalLevelController::class, 'toggleStatus'])->name('approval-levels.toggle-status');
    Route::resource('approval-levels', App\Http\Controllers\Finance\Procurement\ApprovalLevelController::class);
    
    // Tenders
    Route::post('tenders/{id}/publish', [App\Http\Controllers\Finance\Procurement\TenderController::class, 'publish'])->name('tenders.publish');
    Route::post('tenders/{id}/close', [App\Http\Controllers\Finance\Procurement\TenderController::class, 'closeBidding'])->name('tenders.close');
    Route::resource('tenders', App\Http\Controllers\Finance\Procurement\TenderController::class);
    
    // Contracts
    Route::post('contracts/{id}/activate', [App\Http\Controllers\Finance\Procurement\ContractController::class, 'activate'])->name('contracts.activate');
    Route::post('contracts/{id}/complete', [App\Http\Controllers\Finance\Procurement\ContractController::class, 'complete'])->name('contracts.complete');
    Route::post('contracts/{id}/deliverables', [App\Http\Controllers\Finance\Procurement\ContractController::class, 'addDeliverable'])->name('contracts.add-deliverable');
    Route::resource('contracts', App\Http\Controllers\Finance\Procurement\ContractController::class);
});

        // ============ BANK & CASH MANAGEMENT ============
        Route::prefix('bank')->name('bank.')->group(function () {
            Route::prefix('accounts')->name('accounts.')->group(function () {
                Route::get('/', [BankAccountController::class, 'index'])->name('index');
                Route::get('/create', [BankAccountController::class, 'create'])->name('create');
                Route::post('/', [BankAccountController::class, 'store'])->name('store');
                Route::get('/{id}', [BankAccountController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [BankAccountController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BankAccountController::class, 'update'])->name('update');
                Route::post('/{id}/toggle-status', [BankAccountController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{id}/statement', [BankAccountController::class, 'statement'])->name('statement');
            });

            Route::prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/', [BankTransactionController::class, 'index'])->name('index');
                Route::get('/deposit/create', [BankTransactionController::class, 'createDeposit'])->name('deposit.create');
                Route::post('/deposit', [BankTransactionController::class, 'storeDeposit'])->name('deposit.store');
                Route::get('/withdrawal/create', [BankTransactionController::class, 'createWithdrawal'])->name('withdrawal.create');
                Route::post('/withdrawal', [BankTransactionController::class, 'storeWithdrawal'])->name('withdrawal.store');
                Route::get('/transfer', [BankTransactionController::class, 'transfer'])->name('transfer');
                Route::post('/transfer', [BankTransactionController::class, 'transfer'])->name('transfer.post');
                Route::get('/{id}', [BankTransactionController::class, 'show'])->name('show');
            });

            Route::prefix('reconciliation')->name('reconciliation.')->group(function () {
                Route::get('/', [BankReconciliationController::class, 'index'])->name('index');
                Route::get('/create', [BankReconciliationController::class, 'create'])->name('create');
                Route::post('/', [BankReconciliationController::class, 'store'])->name('store');
                Route::get('/{id}', [BankReconciliationController::class, 'show'])->name('show');
                Route::post('/{id}/complete', [BankReconciliationController::class, 'complete'])->name('complete');
            });

            Route::prefix('cashbook')->name('cashbook.')->group(function () {
                Route::get('/', [CashbookController::class, 'index'])->name('index');
                Route::get('/export', [CashbookController::class, 'export'])->name('export');
            });

            Route::prefix('cashflow')->name('cashflow.')->group(function () {
                Route::get('/', [CashFlowController::class, 'index'])->name('index');
                Route::get('/data', [CashFlowController::class, 'getData'])->name('data');
            });
        });

        // ============ EXPORTS ============
        Route::prefix('exports')->name('exports.')->group(function () {
            Route::get('/invoices', [FinanceController::class, 'exportInvoices'])->name('invoices');
            Route::get('/payments', [PaymentController::class, 'exportReport'])->name('payments');
            Route::get('/fee-structures', [FinanceController::class, 'exportFeeStructures'])->name('fee-structures');
        });

        // ============ PROFILE & SETTINGS ============
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [FinanceController::class, 'profile'])->name('index');
            Route::put('/', [FinanceController::class, 'updateProfile'])->name('update');
            Route::get('/password', [FinanceController::class, 'passwordForm'])->name('password');
            Route::post('/password', [FinanceController::class, 'updatePassword'])->name('password.update');
            Route::get('/notifications', [FinanceController::class, 'notificationPreferences'])->name('notifications');
            Route::post('/notifications', [FinanceController::class, 'updateNotificationPreferences'])->name('notifications.update');
        });

        // ============ ALERTS ============
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [FinanceController::class, 'alerts'])->name('index');
            Route::post('/{id}/read', [FinanceController::class, 'markAlertRead'])->name('read');
            Route::post('/read-all', [FinanceController::class, 'markAllAlertsRead'])->name('read-all');
            Route::get('/settings', [FinanceController::class, 'alertSettings'])->name('settings');
            Route::post('/settings', [FinanceController::class, 'updateAlertSettings'])->name('settings.update');
        });


        
    }); // <-- THIS CLOSES THE MAIN FINANCE GROUP