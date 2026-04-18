<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>St.Maximmiliancolbe College - Finance Controller</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/logo.webp') }}" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
    <style>
        /* Sidebar logo sizing */
        .m-header .logo-lg {
            width: 100px;
            height: 40px;
            object-fit: contain;
            display: inline-block;
        }

        .m-header .logo-sm {
            width: 80px;
            height: 80px;
            object-fit: contain;
            display: none;
        }

        /* Sidebar collapse behavior */
        .nxl-navigation.collapsed .logo-lg {
            display: none;
        }

        .nxl-navigation.collapsed .brand-name {
            display: none;
        }

        .nxl-navigation.collapsed .logo-sm {
            display: inline-block;
        }

        /* Brand styling */
        .m-header .b-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 15px;
        }

        /* Main Content Area with Independent Scrolling */
        .main-content {
            min-height: calc(100vh - 70px); /* Full height minus header */
            margin-top: 70px; /* Height of header */
            overflow-y: auto;
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* MOBILE FIX - NO COLLAPSE/EXPAND ANIMATION */
        @media (max-width: 991.98px) {
            /* Sidebar fixed on mobile - always full width overlay */
            .nxl-navigation {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                width: 280px !important;
                z-index: 1050;
                transition: left 0.3s ease;
                background: #fff;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
                overflow-y: auto;
            }
            
            /* When mobile menu is open */
            .nxl-navigation.mobile-open {
                left: 0;
            }
            
            /* Hide collapse/expand toggle buttons on mobile */
            .nxl-navigation-toggle {
                display: none !important;
            }
            
            /* Adjust header padding for mobile */
            .nxl-header {
                padding-left: 0 !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1040;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            /* Overlay when mobile menu is open */
            .mobile-menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1049;
                display: none;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
            
            /* Force sidebar to not collapse on mobile */
            .nxl-navigation:not(.mobile-open) {
                left: -280px;
            }
            
            /* Remove any collapsed styling on mobile */
            .nxl-navigation.collapsed {
                width: 280px !important;
                left: -280px;
            }
            
            .nxl-navigation.collapsed .logo-lg {
                display: inline-block !important;
            }
            
            .nxl-navigation.collapsed .brand-name {
                display: inline !important;
            }
            
            .nxl-navigation.collapsed .logo-sm {
                display: none !important;
            }

            /* Adjust main content for mobile */
            .main-content {
                margin-left: 0 !important;
                margin-top: 70px; /* Account for fixed header */
                padding: 15px;
                width: 100%;
            }
            
            /* Mobile sidebar stays open until user manually closes it */
            .nxl-navigation.mobile-open {
                left: 0;
            }
        }

        /* DESKTOP STYLING */
        @media (min-width: 992px) {
            /* Normal sidebar behavior */
            .nxl-navigation:not(.collapsed) {
                width: 250px;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 1030;
                overflow-y: auto;
            }
            
            .nxl-navigation.collapsed {
                width: 70px;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 1030;
                overflow-y: auto;
            }
            
            /* Smooth transition for desktop */
            .nxl-navigation {
                transition: width 0.3s ease;
            }
            
            /* Fixed header on desktop */
            .nxl-header {
                position: fixed;
                top: 0;
                right: 0;
                left: 250px;
                z-index: 1020;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: left 0.3s ease;
            }
            
            /* Adjust header when sidebar is collapsed */
            body.sidebar-collapsed .nxl-header {
                left: 70px;
            }
            
            /* Adjust main content */
            .main-content {
                margin-left: 250px;
                transition: margin-left 0.3s ease;
            }
            
            body.sidebar-collapsed .main-content {
                margin-left: 70px;
            }
            
            /* Hide mobile overlay on desktop */
            .mobile-menu-overlay {
                display: none !important;
            }
        }

        /* Ensure proper scrolling */
        body {
            overflow-x: hidden;
            background: #f8f9fa;
        }

        /* Footer styling */
        .footer {
            padding: 20px;
            background: #fff;
            border-top: 1px solid #e9ecef;
            margin-top: auto;
        }
        
        /* Submenu styling for mobile */
        .nxl-hasmenu.active .nxl-submenu {
            display: block !important;
        }
        
        /* Finance specific styles */
        .finance-menu-item.active > .nxl-link {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border-left: 3px solid #3498db;
        }

        /* Finance colors */
        :root {
            --finance-primary: #27ae60;
            --finance-secondary: #2c3e50;
        }
        
        .finance-bg {
            background-color: var(--finance-primary);
        }
        
        .finance-text {
            color: var(--finance-primary);
        }

        /* Link styles for student names */
.text-hover-decoration-underline {
    text-decoration: none;
    transition: all 0.2s ease;
}

.text-hover-decoration-underline:hover {
    text-decoration: underline !important;
    color: #0a58ca !important;
}

/* Make sure the avatar and link align properly */
.d-flex.align-items-center.gap-2 {
    flex-wrap: nowrap;
}
    </style>
</head>

<body>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>
  
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Menu !-->
    <!--! ================================================================ !-->
    <!--! ================================================================ !-->
<!--! [Start] Navigation Menu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="m-header py-3">
        <a href="#" class="b-brand d-flex align-items-center justify-content-center">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-lg img-fluid" />
            <span class="brand-name fw-bold text-dark">FINANCE Controller</span>
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-sm img-fluid" />
        </a>
    </div>

    <div class="navbar-content">
        <ul class="nxl-navbar">

            <li class="nxl-item nxl-caption">
                <label>ENTERPRISE FINANCE SYSTEM</label>
            </li>

            <!-- 1️⃣ Finance Dashboard -->
            <li class="nxl-item">
                <a href="{{ route('finance.dashboard') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-home"></i></span>
                    <span class="nxl-mtext">Finance Dashboard</span>
                </a>
            </li>

            <!-- 2️⃣ Revenue Management -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
        <span class="nxl-mtext">Revenue Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.fee-structures.index') }}">Fee Structures</a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.invoices.create') }}">Generate Invoice</a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.invoices.index') }}">Control Numbers</a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.student-statements.index') }}">Student Statements</a>
        </li>
        
    </ul>
</li>

            <!-- 3️⃣ Accounts Receivable -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
        <span class="nxl-mtext">Accounts Receivable</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-receivable.outstanding') }}">
                Outstanding Invoices
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-receivable.aging') }}">
                Aging Report
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.credit-notes.index') }}">
                Credit Notes
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.refunds.index') }}">
                Refund Processing
            </a>
        </li>
    </ul>
</li>

<!-- 2️⃣ Payments Management -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
        <span class="nxl-mtext">Payments Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <!-- ===== DASHBOARD & OVERVIEW ===== -->
        <li class="nxl-item nxl-caption">
            <label>OVERVIEW</label>
        </li>
        
        <!-- All Payments -->
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.all-payments.index') }}">
                <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                <span class="nxl-mtext">All Payments</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payment-adjustments.my-requests') }}">
                <span class="nxl-micon"><i class="feather-file-text"></i></span>
                <span class="nxl-mtext"> Adjustment Requests</span>
            </a>
        </li>
       

     <!-- Payment Filter -->
<li class="nxl-item">
    <a class="nxl-link" href="{{ route('finance.payment-filter.index') }}">
        <span class="nxl-micon"><i class="feather-filter"></i></span>
        <span class="nxl-mtext">Payment Filter</span>
    </a>
</li>
        
        <!-- ===== STUDENT INFORMATION ===== -->
        <li class="nxl-item nxl-caption">
            <label>STUDENT INFORMATION</label>
        </li>
        
        <!--  STUDENT PAYMENT INFO - HII NDIYO UNAYOTAKA -->
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.student-payment-info.search') }}">
                <span class="nxl-micon"><i class="feather-file-text"></i></span>
                <span class="nxl-mtext">Student Payment Info</span>
            </a>
        </li>
        
        
        <!-- Student Statements -->
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.student-statements.index') }}">
                <span class="nxl-micon"><i class="feather-file"></i></span>
                <span class="nxl-mtext">Student Statements</span>
            </a>
        </li>
        
        <!-- ===== PAYMENTS BY TYPE ===== -->
        <li class="nxl-item nxl-caption">
            <label>PAYMENTS BY TYPE</label>
        </li>
        
        <!-- Fee Types -->
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payments-management.fee-type', 'tuition') }}">
                <span class="nxl-micon"><i class="feather-book"></i></span>
                <span class="nxl-mtext">Tuition</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payments-management.fee-type', 'hostel') }}">
                <span class="nxl-micon"><i class="feather-home"></i></span>
                <span class="nxl-mtext">Hostel</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payments-management.fee-type', 'supplementary') }}">
                <span class="nxl-micon"><i class="feather-edit"></i></span>
                <span class="nxl-mtext">Supplementary</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payments-management.fee-type', 'repeat') }}">
                <span class="nxl-micon"><i class="feather-repeat"></i></span>
                <span class="nxl-mtext">Repeat Modules</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.payments-management.fee-type', 'special') }}">
                <span class="nxl-micon"><i class="feather-star"></i></span>
                <span class="nxl-mtext">Special Fees</span>
            </a>
        </li>
        
        
    </ul>
</li>
            <!-- 4️⃣ Accounts Payable -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-shopping-cart"></i></span>
        <span class="nxl-mtext">Accounts Payable</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-payable.suppliers.index') }}">
                <span class="nxl-micon"><i class="feather-users"></i></span>
                <span class="nxl-mtext">Suppliers</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-payable.purchase-orders.index') }}">
                <span class="nxl-micon"><i class="feather-shopping-cart"></i></span>
                <span class="nxl-mtext">Purchase Orders</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-payable.grn.index') }}">
                <span class="nxl-micon"><i class="feather-package"></i></span>
                <span class="nxl-mtext">Goods Received</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-payable.invoices.index') }}">
                <span class="nxl-micon"><i class="feather-file-text"></i></span>
                <span class="nxl-mtext">Supplier Invoices</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.accounts-payable.payment-vouchers.index') }}">
                <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                <span class="nxl-mtext">Payments</span>
            </a>
        </li>
    </ul>
</li>

            <!-- 5️⃣ Budget Management -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-pie-chart"></i></span>
        <span class="nxl-mtext">Budget Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.budget.years.index') }}">
                <span class="nxl-micon"><i class="feather-calendar"></i></span>
                <span class="nxl-mtext">Budget Years</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.budget.years.create') }}">
                <span class="nxl-micon"><i class="feather-plus"></i></span>
                <span class="nxl-mtext">New Budget</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.budget.years.index') }}?status=active">
                <span class="nxl-micon"><i class="feather-check-circle"></i></span>
                <span class="nxl-mtext">Active Budgets</span>
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.budget.years.index') }}?status=draft">
                <span class="nxl-micon"><i class="feather-clock"></i></span>
                <span class="nxl-mtext">Pending Approval</span>
            </a>
        </li>
    </ul>
</li>
           <!-- 6️⃣ General Ledger -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-book"></i></span>
        <span class="nxl-mtext">General Ledger</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.general-ledger.chart-of-accounts.index') }}">
                Chart of Accounts
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.general-ledger.journal-entries.index') }}">
                Journal Entries
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.general-ledger.trial-balance.index') }}">
                Trial Balance
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.general-ledger.ledger-reports.index') }}">
                Ledger Reports
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.general-ledger.fiscal-years.index') }}">
                Fiscal Years
            </a>
        </li>
    </ul>
</li>

            <!-- 7️⃣ Bank & Cash -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-briefcase"></i></span>
        <span class="nxl-mtext">Bank & Cash Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.bank.accounts.index') }}">
                <i class="feather-university me-2"></i>Bank Accounts
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.bank.reconciliation.index') }}">
                <i class="feather-check-circle me-2"></i>Bank Reconciliation
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.bank.cashbook.index') }}">
                <i class="feather-book-open me-2"></i>Cashbook
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.bank.cashflow.index') }}">
                <i class="feather-trending-up me-2"></i>Cash Flow Monitoring
            </a>
        </li>
    </ul>
</li>

            <!-- 8️⃣ Financial Reporting -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-file-text"></i></span>
        <span class="nxl-mtext">Financial Reporting</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.reporting.income-statement') }}">
                <i class="feather-trending-up me-2"></i>Income Statement
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.reporting.balance-sheet') }}">
                <i class="feather-layers me-2"></i>Balance Sheet
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.reporting.cash-flow') }}">
                <i class="feather-activity me-2"></i>Cash Flow Statement
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.reporting.departments') }}">
                <i class="feather-users me-2"></i>Department Reports
            </a>
        </li>
    </ul>
</li>

<!-- 9️⃣ Audit & Compliance -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-shield"></i></span>
        <span class="nxl-mtext">Audit & Compliance</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.audit.audit-trail') }}">
                <i class="feather-list me-2"></i>Audit Trail
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.audit.transaction-logs') }}">
                <i class="feather-credit-card me-2"></i>Transaction Logs
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.audit.role-activity') }}">
                <i class="feather-users me-2"></i>Role Activity Logs
            </a>
        </li>
    </ul>
</li>

            <!-- 🔟 Payroll Management -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-users"></i></span>
        <span class="nxl-mtext">Payroll Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item"><a class="nxl-link" href="#">Staff List</a></li>
        <li class="nxl-item"><a class="nxl-link" href="#">Salary Structure</a></li>
        <li class="nxl-item"><a class="nxl-link" href="#">Payroll Processing</a></li>
        <li class="nxl-item"><a class="nxl-link" href="#">PAYE & Deductions</a></li>
        <li class="nxl-item"><a class="nxl-link" href="#">Payslips</a></li>
    </ul>
</li>

<!-- 1️⃣1️⃣ Asset Management -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-box"></i></span>
        <span class="nxl-mtext">Asset Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.assets.index') }}">
                <i class="feather-list me-2"></i>Asset Register
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.assets.create') }}">
                <i class="feather-plus-circle me-2"></i>Add New Asset
            </a>
        </li>
        <li class="nxl-item dropdown-divider"></li>
        
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.categories.index') }}">
                <i class="feather-tag me-2"></i>Asset Categories
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.depreciation.index') }}">
                <i class="feather-trending-down me-2"></i>Depreciation
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.disposals.index') }}">
                <i class="feather-trash-2 me-2"></i>Asset Disposal
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.transfers.index') }}">
                <i class="feather-move me-2"></i>Asset Transfer
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.asset.transfers.pending') }}">
                <i class="feather-clock me-2"></i>Pending Transfers
            </a>
        </li>
    </ul>
</li>

<!-- 1️⃣3️⃣ Procurement & Workflow -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-clipboard"></i></span>
        <span class="nxl-mtext">Procurement Workflow</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.procurement.requisitions.index') }}">
                <i class="feather-file-text me-2"></i>Requisition Requests
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.procurement.approval-levels.index') }}">
                <i class="feather-layers me-2"></i>Approval Levels
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.procurement.tenders.index') }}">
                <i class="feather-git-pull-request me-2"></i>Tender Management
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('finance.procurement.contracts.index') }}">
                <i class="feather-file-plus me-2"></i>Contract Management
            </a>
        </li>
    </ul>
</li>


            <li class="nxl-item dropdown-divider"></li>

            <!-- Logout -->
            <li class="nxl-item">
                <a class="nxl-link" href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="nxl-micon"><i class="feather-log-out"></i></span>
                    <span class="nxl-mtext">Logout</span>
                </a>
            </li>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>

        </ul>
    </div>
</nav>

<!--! ================================================================ !-->
<!--! [End]  Navigation Menu !-->
<!--! ================================================================ !-->
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Menu !-->
    <!--! ================================================================ !-->
    
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [End] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                       <i class="feather-menu"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                    
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    
                    <div class="dropdown nxl-h-item">
                        <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                            <i class="feather-bell"></i>
                            <span class="badge bg-danger nxl-h-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                            <div class="d-flex justify-content-between align-items-center notifications-head">
                                <h6 class="fw-bold text-dark mb-0">Finance Notifications</h6>
                                <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip" title="Make as Read">
                                    <i class="feather-check"></i>
                                    <span>Make as Read</span>
                                </a>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">New Fee Structure Pending Approval</a>
                                    <span class="notifications-meta">5 min ago</span>
                                </div>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">Control Number Request</a>
                                    <span class="notifications-meta">15 min ago</span>
                                </div>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">Monthly Revenue Report Ready</a>
                                    <span class="notifications-meta">1 hour ago</span>
                                </div>
                            </div>
                            <div class="text-center notifications-footer">
                                <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">All Notifications</a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown nxl-h-item">
                      @php
    $user = Auth::user();
    
    if (!$user) {
        $avatar = asset('assets/images/avatar/1.png');
        $displayName = 'Guest User';
        $displayEmail = 'guest@example.com';
        $displayType = 'GUEST';
    } else {
        // MUHIMU: Angalia kama profile_photo ipo kwenye database
        if (!empty($user->profile_photo)) {
            // Tumia asset() moja kwa moja na storage path
            $avatar = asset('storage/' . $user->profile_photo);
            
            // Debug - unaweza kuona URL (remove baada ya kufanya kazi)
            // echo "<!-- Avatar URL: " . $avatar . " -->";
        } else {
            // Default UI Avatar
            $name = urlencode($user->first_name . ' ' . $user->last_name);
            $avatar = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF&size=128&bold=true";
        }
        
        $displayName = $user->first_name . ' ' . $user->last_name;
        $displayEmail = $user->email;
        $displayType = strtoupper($user->user_type ?? 'ADMIN');
    }
@endphp


<a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
    <img src="{{ asset($avatar) }}" alt="user-image" class="img-fluid user-avtar me-0" />
</a>

                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                              @php
    $user = Auth::user();
    
    if (!$user) {
        $avatar = asset('assets/images/avatar/1.png');
        $displayName = 'Guest User';
        $displayEmail = 'guest@example.com';
        $displayType = 'GUEST';
    } else {
        // MUHIMU: Angalia kama profile_photo ipo kwenye database
        if (!empty($user->profile_photo)) {
            // Tumia asset() moja kwa moja na storage path
            $avatar = asset('storage/' . $user->profile_photo);
            
            // Debug - unaweza kuona URL (remove baada ya kufanya kazi)
            // echo "<!-- Avatar URL: " . $avatar . " -->";
        } else {
            // Default UI Avatar
            $name = urlencode($user->first_name . ' ' . $user->last_name);
            $avatar = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF&size=128&bold=true";
        }
        
        $displayName = $user->first_name . ' ' . $user->last_name;
        $displayEmail = $user->email;
        $displayType = strtoupper($user->user_type ?? 'ADMIN');
    }
@endphp


<div class="d-flex align-items-center">
    <img src="{{ asset($avatar) }}" alt="user-image" class="img-fluid user-avtar" />
    <div>
        <h6 class="text-dark mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
        <span class="fs-12 fw-medium text-muted">{{ $user->email }}</span>
        <span class="badge finance-bg text-white mt-1">Finance Controller</span>
    </div>
</div>

                            </div>
                            
                            <div class="dropdown-divider"></div>

<a href="javascript:void(0);" class="dropdown-item">
    <i class="feather-user"></i>
    <span>Profile Details</span>
</a>

<a href="javascript:void(0);" class="dropdown-item">
    <i class="feather-settings"></i>
    <span>Account Settings</span>
</a>

<div class="dropdown-divider"></div>

<a href="{{ route('logout') }}" 
   onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
   class="dropdown-item">
    <i class="feather-log-out"></i>
    <span>Logout</span>
</a>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

                        </div>
                    </div>
                </div>
            </div>
            <!--! [End] Header Right !-->
        </div>
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->
    
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    
    <!-- Main content area with independent scrolling -->
    <div class="main-content">
        @yield('content')
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
<!--! Footer Script !-->
<!--! ================================================================ !-->
<!--! BEGIN: Vendors JS !-->
<!-- Vendors JS (MUST be on top) -->
<script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>

<!-- Check if this is dashboard page -->
@php
    $isDashboardPage = request()->routeIs('finance.dashboard') || request()->is('finance/dashboard');
@endphp

@if($isDashboardPage)
<!-- Only load chart libraries on dashboard page -->
<script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
<script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
@endif

<!-- App Init -->
<script src="{{ asset('assets/js/common-init.min.js') }}"></script>

<!-- Theme Customizer -->
<script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>

<!-- Custom Mobile Menu Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileCollapseBtn = document.getElementById('mobile-collapse');
        const sidebar = document.querySelector('.nxl-navigation');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const menuMiniButton = document.getElementById('menu-mini-button');
        const menuExpendButton = document.getElementById('menu-expend-button');
        const body = document.body;
        const header = document.querySelector('.nxl-header');
        
        // Mobile menu functionality
        if (mobileCollapseBtn && sidebar && mobileOverlay) {
            mobileCollapseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('active');
                body.classList.toggle('mobile-menu-open');
                
                // Prevent scrolling on body when menu is open
                if (sidebar.classList.contains('mobile-open')) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            });
            
            // Close menu when clicking overlay
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                body.classList.remove('mobile-menu-open');
                body.style.overflow = '';
            });
        }
        
        // Desktop collapse/expand functionality
        if (menuMiniButton && menuExpendButton && sidebar && header) {
            menuMiniButton.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
                menuMiniButton.style.display = 'none';
                menuExpendButton.style.display = 'block';
                
                // Update header position
                if (window.innerWidth >= 992) {
                    header.style.left = '70px';
                }
            });
            
            menuExpendButton.addEventListener('click', function() {
                sidebar.classList.remove('collapsed');
                body.classList.remove('sidebar-collapsed');
                menuExpendButton.style.display = 'none';
                menuMiniButton.style.display = 'block';
                
                // Update header position
                if (window.innerWidth >= 992) {
                    header.style.left = '250px';
                }
            });
            
            // Check screen size on load and resize
            function checkScreenSize() {
                if (window.innerWidth < 992) {
                    // On mobile, hide desktop toggle buttons
                    menuMiniButton.style.display = 'none';
                    menuExpendButton.style.display = 'none';
                    // Make sure sidebar is not collapsed on mobile
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    // Reset header position
                    header.style.left = '0';
                } else {
                    // On desktop, show appropriate toggle button
                    if (sidebar.classList.contains('collapsed')) {
                        menuMiniButton.style.display = 'none';
                        menuExpendButton.style.display = 'block';
                        header.style.left = '70px';
                    } else {
                        menuMiniButton.style.display = 'block';
                        menuExpendButton.style.display = 'none';
                        header.style.left = '250px';
                    }
                }
            }
            
            // Initial check
            checkScreenSize();
            
            // Check on resize
            window.addEventListener('resize', checkScreenSize);
        }
        
        // Prevent body scroll when sidebar is open on mobile
        document.addEventListener('touchmove', function(e) {
            if (window.innerWidth < 992 && sidebar.classList.contains('mobile-open')) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // Handle submenu toggling on mobile
        const menuItems = document.querySelectorAll('.nxl-hasmenu > .nxl-link');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    // On mobile, toggle submenu visibility
                    e.preventDefault();
                    const parent = this.parentElement;
                    parent.classList.toggle('active');
                }
                // On desktop, default behavior is maintained
            });
        });
        
        // Set active menu item based on current URL
        const currentPath = window.location.pathname;
        const menuItemsAll = document.querySelectorAll('.finance-menu-item');
        
        // First, remove all active classes
        menuItemsAll.forEach(item => {
            item.classList.remove('active');
        });
        
        // Then set active based on current path
        menuItemsAll.forEach(item => {
            const link = item.querySelector('.nxl-link');
            if (link) {
                const href = link.getAttribute('href');
                
                if (href === '#' && currentPath.includes('/finance/dashboard')) {
                    // Dashboard link
                    item.classList.add('active');
                } else if (href && href !== 'javascript:void(0);' && currentPath.includes(href)) {
                    // Other links
                    item.classList.add('active');
                }
            }
        });
        
        // Also set parent menu as active if submenu item is active
        menuItemsAll.forEach(item => {
            const submenuLinks = item.querySelectorAll('.nxl-submenu .nxl-link');
            let hasActiveChild = false;
            
            submenuLinks.forEach(subLink => {
                const subHref = subLink.getAttribute('href');
                if (subHref && subHref !== 'javascript:void(0);' && currentPath.includes(subHref)) {
                    hasActiveChild = true;
                }
            });
            
            if (hasActiveChild) {
                item.classList.add('active');
            }
        });
    });
    
    // Prevent ApexCharts errors if loaded on non-dashboard pages
    if (typeof ApexCharts !== 'undefined') {
        // Check if we're on a page with chart elements
        const chartElements = [
            'revenueChart',
            'collectionsChart',
            'outstandingChart'
        ];
        
        let hasChartElement = false;
        chartElements.forEach(id => {
            if (document.getElementById(id)) {
                hasChartElement = true;
            }
        });
        
        // If no chart elements found, override ApexCharts to prevent errors
        if (!hasChartElement) {
            const originalInit = ApexCharts.prototype.init;
            ApexCharts.prototype.init = function() {
                // Only initialize if element exists
                if (this.el && document.body.contains(this.el)) {
                    return originalInit.apply(this, arguments);
                }
                return null;
            };
        }
    }


    function selectBudgetYear(type) {
    // Get active budget years
    fetch('{{ route("finance.budget.years.index") }}?status=active&format=json')
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                // No active budget years
                Swal.fire({
                    title: 'No Active Budget Year',
                    text: 'Please select or create a budget year first',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'View All Budget Years',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("finance.budget.years.index") }}';
                    }
                });
            } else if (data.length === 1) {
                // One active budget year - go directly
                const budgetId = data[0].id;
                let url = '';
                
                switch(type) {
                    case 'departments':
                        url = '{{ route("finance.budget.departments.index", "") }}/' + budgetId;
                        break;
                    case 'items':
                        url = '{{ route("finance.budget.items.index", "") }}/' + budgetId;
                        break;
                    case 'vs-actual':
                        url = '{{ route("finance.budget.years.vs-actual", "") }}/' + budgetId;
                        break;
                    case 'revisions':
                        url = '{{ route("finance.budget.revisions.index", "") }}/' + budgetId;
                        break;
                }
                
                window.location.href = url;
            } else {
                // Multiple active budget years - show selection
                let options = '<select id="budgetYearSelect" class="form-select">';
                data.forEach(year => {
                    options += `<option value="${year.id}">${year.name}</option>`;
                });
                options += '</select>';
                
                Swal.fire({
                    title: 'Select Budget Year',
                    html: options,
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    preConfirm: () => {
                        const budgetId = document.getElementById('budgetYearSelect').value;
                        let url = '';
                        
                        switch(type) {
                            case 'departments':
                                url = '{{ route("finance.budget.departments.index", "") }}/' + budgetId;
                                break;
                            case 'items':
                                url = '{{ route("finance.budget.items.index", "") }}/' + budgetId;
                                break;
                            case 'vs-actual':
                                url = '{{ route("finance.budget.years.vs-actual", "") }}/' + budgetId;
                                break;
                            case 'revisions':
                                url = '{{ route("finance.budget.revisions.index", "") }}/' + budgetId;
                                break;
                        }
                        
                        window.location.href = url;
                    }
                });
            }
        });
}
</script>

@stack('scripts')

</body>

</html>