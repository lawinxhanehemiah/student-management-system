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
    <title>St.Maximmiliancolbe College - Admission</title>
    <!-- REMOVE THIS CDN LINE -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    
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
        
        /* Admission specific styles */
        .admission-menu-item.active > .nxl-link {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border-left: 3px solid #3498db;
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
            <span class="brand-name fw-bold text-dark">ADMISSION Officer</span>
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-sm img-fluid" />
        </a>
    </div>

    <div class="navbar-content">
        <ul class="nxl-navbar">
            <li class="nxl-item nxl-caption">
                <label>Admission Dashboard</label>
            </li>
            
            <!-- Dashboard -->
            <li class="nxl-item admission-menu-item">
                <a href="{{ route('admission.dashboard') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-home"></i></span>
                    <span class="nxl-mtext">Dashboard</span>
                </a>
            </li>

            <!-- Applicants -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-users"></i></span>
                    <span class="nxl-mtext">Applicants</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applicants.index') }}">
                            All Applicants
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applicants.pending-review') }}">
                            Pending
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="">
                            Verified
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applicants.rejected') }}">
                            Rejected
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Eligibility Engine -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-shield"></i></span>
        <span class="nxl-mtext">Eligibility Engine</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.eligibility.rules') }}">
                <i class="feather-settings"></i> Eligibility Rules
            </a>
        </li>
    </ul>
</li>

            <!-- Applications -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-file-text"></i></span>
                    <span class="nxl-mtext">Applications</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applications.create') }}">
                            New Applications
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applicants.index') }}">
                            Online Applications
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Application Forms
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-file-text"></i></span>
        <span class="nxl-mtext">Application Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.index') }}">
                <i class="feather-list"></i> All Applications
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.drafts') }}">
                <i class="feather-file-minus"></i> Draft Applications
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.create') }}">
                <i class="feather-plus-circle"></i> Create New Application
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.index', ['status' => 'submitted']) }}">
                <i class="feather-send"></i> Submitted Applications
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.index', ['status' => 'approved']) }}">
                <i class="feather-check-circle"></i> Approved Applications
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.officer.applications.index', ['status' => 'rejected']) }}">
                <i class="feather-x-circle"></i> Rejected Applications
            </a>
        </li>
    </ul>
</li>
<!-- Intake Management -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-calendar"></i></span>
        <span class="nxl-mtext">Intake Management</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.intakes.index') }}">
                <i class="feather-list"></i> All Intakes
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.intakes.create') }}">
                <i class="feather-plus"></i> Create Intake
            </a>
        </li>
    </ul>
</li>
            <!-- Programs -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-book"></i></span>
                    <span class="nxl-mtext">Programs</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.programs') }}">
                            Programs
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Intakes
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Program Requirements
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Program Capacity
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Selection -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-check-square"></i></span>
                    <span class="nxl-mtext">Selection</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.selection.ranking') }}">
                            Applicant Ranking
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.selection.selected') }}">
                            Selected Students
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.applicants.waitlisted') }}">
                            Waiting List
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Admission Offers -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-mail"></i></span>
                    <span class="nxl-mtext">Admission Offers</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.offers.letters') }}">
                            Offer Letters
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.offers.acceptance') }}">
                            Offer Acceptance
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="{{ route('admission.offers.enrollment') }}">
                            Enrollment
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Documents -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-folder"></i></span>
        <span class="nxl-mtext">Documents</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.documents.index') }}">
                <i class="feather-list"></i> Uploaded Documents
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.documents.verification-queue') }}">
                <i class="feather-check-circle"></i> Verification Queue
            </a>
        </li>
    </ul>
</li>

            <!-- Payments -->
            <li class="nxl-item nxl-hasmenu admission-menu-item">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                    <span class="nxl-mtext">Payments</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Application Fees
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Payment Verification
                        </a>
                    </li>
                    <li class="nxl-item">
                        <a class="nxl-link" href="#">
                            Receipts
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Students -->
            <li class="nxl-item nxl-hasmenu admission-menu-item {{ request()->routeIs('admission.students.*') ? 'active' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-user-check"></i></span>
                    <span class="nxl-mtext">Students</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li class="nxl-item {{ request()->routeIs('admission.students.index') ? 'active' : '' }}">
                        <a class="nxl-link" href="{{ route('admission.students.index') }}">
                            Registered Students
                        </a>
                    </li>
                </ul>
            </li>

           <!-- Reports -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
        <span class="nxl-mtext">Reports</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.reports.statistics') }}">
                <i class="feather-pie-chart"></i> Admission Statistics
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.reports.program-statistics') }}">
                <i class="feather-book"></i> Program Statistics
            </a>
        </li>
    </ul>
</li>
    <!-- Inquiries -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-phone-call"></i></span>
        <span class="nxl-mtext">Inquiries</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.inquiries.create') }}"><i class="feather-plus-circle"></i> New Inquiry</a></li>
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.inquiries.index') }}"><i class="feather-list"></i> Inquiry List</a></li>
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.inquiries.follow-ups') }}"><i class="feather-clock"></i> Follow-up Logs</a></li>
    </ul>
</li>

<!-- Letters & Docs -->
<li class="nxl-item nxl-hasmenu">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-printer"></i></span>
        <span class="nxl-mtext">Letters & Docs</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.documents.admission-letters') }}"><i class="feather-file-text"></i> Admission Letters</a></li>
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.documents.id-card-requests') }}"><i class="feather-credit-card"></i> ID Card Request</a></li>
        <li class="nxl-item"><a class="nxl-link" href="{{ route('admission.documents.archive') }}"><i class="feather-folder"></i> Document Archive</a></li>
    </ul>
</li>

            <!-- Settings -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-settings"></i></span>
        <span class="nxl-mtext">Settings</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.settings.calendar') }}">
                <i class="feather-calendar"></i> Admission Calendar
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.settings.workflow') }}">
                <i class="feather-git-branch"></i> Workflow
            </a>
        </li>
    </ul>
</li>

<!-- Support & Help -->
<li class="nxl-item nxl-hasmenu admission-menu-item">
    <a href="javascript:void(0);" class="nxl-link">
        <span class="nxl-micon"><i class="feather-help-circle"></i></span>
        <span class="nxl-mtext">Support & Help</span>
        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
    </a>
    <ul class="nxl-submenu">
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.support.help-center') }}">
                <i class="feather-book-open"></i> Help Center
            </a>
        </li>
        <li class="nxl-item">
            <a class="nxl-link" href="{{ route('admission.support.faq') }}">
                <i class="feather-help-circle"></i> FAQ
            </a>
        </li>
    </ul>
</li>
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
                                <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                                <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip" title="Make as Read">
                                    <i class="feather-check"></i>
                                    <span>Make as Read</span>
                                </a>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">New Application Received</a>
                                    <span class="notifications-meta">5 min ago</span>
                                </div>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">Application Review Required</a>
                                    <span class="notifications-meta">15 min ago</span>
                                </div>
                            </div>
                            <div class="notifications-item">
                                <div class="notifications-content">
                                    <a href="javascript:void(0);" class="notifications-subtitle">Admission Letter Generated</a>
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
    $avatar = $user->profile_photo
        ? 'storage/profile_photos/' . $user->profile_photo
        : 'assets/images/default.png';
@endphp

<a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
    <img src="{{ asset($avatar) }}" alt="user-image" class="img-fluid user-avtar me-0" />
</a>

                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                @php
    $user = Auth::user();
    $avatar = $user->avatar ?? 'assets/images/default.png'; // default image
@endphp

<div class="d-flex align-items-center">
    <img src="{{ asset($avatar) }}" alt="user-image" class="img-fluid user-avtar" />
    <div>
        <h6 class="text-dark mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
        <span class="fs-12 fw-medium text-muted">{{ $user->email }}</span>
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
    $isDashboardPage = request()->routeIs('admission.dashboard') || request()->is('admission/dashboard');
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
        const menuItemsAll = document.querySelectorAll('.admission-menu-item');
        
        // First, remove all active classes
        menuItemsAll.forEach(item => {
            item.classList.remove('active');
        });
        
        // Then set active based on current path
        menuItemsAll.forEach(item => {
            const link = item.querySelector('.nxl-link');
            if (link) {
                const href = link.getAttribute('href');
                
                if (href === '#' && currentPath.includes('/admission/dashboard')) {
                    // Dashboard link
                    item.classList.add('active');
                } else if (href && href !== 'javascript:void(0);' && currentPath.includes(href)) {
                    // Other links
                    item.classList.add('active');
                } else if (href && href !== 'javascript:void(0);' && href.includes('/admission/applications/create') && 
                           currentPath.includes('/admission/applications/create')) {
                    // Create application link
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
            'applicationChart',
            'enrollmentChart',
            'feeCollectionChart'
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
</script>

@stack('scripts')

</body>

</html>