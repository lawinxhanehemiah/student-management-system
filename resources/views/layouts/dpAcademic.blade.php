<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>St.Maximmiliancolbe  College </title>

<!-- Favicon -->
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/logo.webp') }}" />

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />

<!-- Theme CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />

    <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
    <!--[if lt IE 9]>
            <script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
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
    </style>
</head>

<body>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>
  
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Menu !-->
    <!--! ================================================================ !-->
<nav class="nxl-navigation">
    <!-- Header -->
    <div class="m-header py-3">
        <a href="{{ route('dp.academics.dashboard') }}" class="b-brand d-flex align-items-center justify-content-center">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-lg img-fluid" />
            <span class="brand-name fw-bold text-dark ms-2">CBET MANAGER <small class="text-primary">v2.0</small></span>
            <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-sm img-fluid" />
        </a>
    </div>

    <!-- Search Bar (Improved) -->
    <div class="px-3 mt-2 mb-3">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent"><i class="feather-search"></i></span>
            <input type="text" class="form-control" id="menuSearch" placeholder="Search menu...">
        </div>
    </div>

    <div class="navbar-content">
        <ul class="nxl-navbar" id="mainMenu">

            

            

            <!-- Dashboard -->
            <li class="nxl-item {{ request()->routeIs('dp.academics.dashboard') ? 'active' : '' }}">
                <a href="{{ route('dp.academics.dashboard') }}" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-home"></i></span>
                    <span class="nxl-mtext">Dashboard</span>
                </a>
            </li>

            <!-- ================== SECTION 1: ACADEMIC CALENDAR ================== -->
            <li class="nxl-item nxl-caption">
                <label>ACADEMIC CALENDAR</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.calendar.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-calendar"></i></span>
                    <span class="nxl-mtext">Semester Control</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link ">Manage Semesters</a></li>
                    <li><a class="nxl-link ">Exam Periods</a></li>
                    <li><a class="nxl-link ">Registration Windows</a></li>
                    <li><a class="nxl-link ">Set Active Semester</a></li>
                </ul>
            </li>

            <!-- Curriculum Architecture -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.curriculum.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-book-open"></i></span>
                    <span class="nxl-mtext">Curriculum Architecture</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="{{ route('dp.academics.curriculum.nta-levels') }}">NTA Level Mapping (4‑8)</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.curriculum.competence-elements') }}">Competence Standards</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.curriculum.versioning') }}">Curriculum Version Control</a></li>
                </ul>
            </li>

            <!-- Programmes & Departments -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.programmes.*') || request()->routeIs('dp.academics.departments.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-layers"></i></span>
                    <span class="nxl-mtext">Programmes & Departments</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="}">All Programmes</a></li>
                    <li><a class="nxl-link" href="">Departments</a></li>
                    <li><a class="nxl-link" href="">Department Performance</a></li>
                </ul>
            </li>

            <!-- Courses & Timetable -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.courses.*') || request()->routeIs('dp.academics.timetable.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-clock"></i></span>
                    <span class="nxl-mtext">Courses & Timetable</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Course Catalogue</a></li>
                    <li><a class="nxl-link" href="">Electives Management</a></li>
                    <li><a class="nxl-link" href="">Institution Timetable</a></li>
                </ul>
            </li>

            <!-- Enrollment Control -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.avn.*') || request()->routeIs('dp.academics.students.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-user-check"></i></span>
                    <span class="nxl-mtext">Enrollment Control</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="{{ route('dp.academics.avn.verification') }}">AVN Validation Engine @if(isset($pendingVerificationCount) && $pendingVerificationCount > 0)<span class="badge bg-warning text-dark ms-2">{{ $pendingVerificationCount }}</span>@endif</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.students.financial-clearance') }}">Financial Gatekeeping</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.students.active-registry') }}">Student Portal Access</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 3: ASSESSMENT & RESULTS ================== -->
            <li class="nxl-item nxl-caption">
                <label>ASSESSMENT & RESULTS</label>
            </li>

            <!-- Assessment Management (Marking, Moderation, Grading, Locking) -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.assessment.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-check-square"></i></span>
                    <span class="nxl-mtext">Assessment Management</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="{{ route('dp.academics.assessment.marks-entry') }}">Continuous Assessment (CA)</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.assessment.moderation') }}">Internal/External Moderation @if(isset($pendingModerationCount) && $pendingModerationCount > 0)<span class="badge bg-danger ms-2">{{ $pendingModerationCount }}</span>@endif</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.assessment.grading-engine') }}">CBET Grading (C/NYC)</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.assessment.locking') }}">Lock Results (Finalize)</a></li>
                </ul>
            </li>

            <!-- Result Publishing (NEW) -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.publishing.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-eye"></i></span>
                    <span class="nxl-mtext">Result Publishing</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Publish to Students</a></li>
                    <li><a class="nxl-link" href="">Unpublish / Withdraw</a></li>
                    <li><a class="nxl-link" href="">Publication History</a></li>
                </ul>
            </li>

            <!-- Re-assessment Module -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.reassessment.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-refresh-cw"></i></span>
                    <span class="nxl-mtext">Re-Assessment Module</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="{{ route('dp.academics.reassessment.eligible') }}">NYC Student List @if(isset($nycStudentCount) && $nycStudentCount > 0)<span class="badge bg-warning text-dark ms-2">{{ $nycStudentCount }}</span>@endif</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.reassessment.scheduling') }}">Scheduling & Retakes</a></li>
                    <li><a class="nxl-link" href="{{ route('dp.academics.reassessment.supplementary') }}">Supplementary Board</a></li>
                </ul>
            </li>

            <!-- Transcript & Certification -->
            <li class="nxl-item {{ request()->routeIs('dp.academics.transcripts.*') ? 'active' : '' }}">
                <a href="" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-file-text"></i></span>
                    <span class="nxl-mtext">Transcript & Certification</span>
                    @if(isset($pendingTranscriptsCount) && $pendingTranscriptsCount > 0)
                        <span class="badge bg-danger ms-auto">{{ $pendingTranscriptsCount }}</span>
                    @endif
                </a>
            </li>

            <!-- ================== SECTION 4: GRADUATION ENGINE ================== -->
            <li class="nxl-item nxl-caption">
                <label>GRADUATION ENGINE</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.graduation.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-award"></i></span>
                    <span class="nxl-mtext">Graduation Management</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Eligible Students</a></li>
                    <li><a class="nxl-link" href="">Clearance Status</a></li>
                    <li><a class="nxl-link" href="">Confer Graduation</a></li>
                    <li><a class="nxl-link" href="">Ceremony Setup</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 5: INDUSTRIAL TRAINING (IPT) ================== -->
            <li class="nxl-item nxl-caption">
                <label>INDUSTRIAL TRAINING (IPT)</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.ipt.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                    <span class="nxl-mtext">IPT Management</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">IPT Logbooks & Placement</a></li>
                    <li><a class="nxl-link" href="">Field Supervisor Scoring</a></li>
                    <li><a class="nxl-link" href="">IPT Completion Reports</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 6: STUDENT PORTAL (ACTUAL) ================== -->
            <li class="nxl-item nxl-caption">
                <label>STUDENT PORTAL</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.portal.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-users"></i></span>
                    <span class="nxl-mtext">Student Experience</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Student Dashboard (Preview)</a></li>
                    <li><a class="nxl-link" href="">Student Results View</a></li>
                    <li><a class="nxl-link" href="">Student IPT Status</a></li>
                    <li><a class="nxl-link" href="">Progression Status</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 7: NOTIFICATIONS & ALERTS ================== -->
            <li class="nxl-item nxl-caption">
                <label>NOTIFICATIONS & ALERTS</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.notifications.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-bell"></i></span>
                    <span class="nxl-mtext">Alert System</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Send Broadcast</a></li>
                    <li><a class="nxl-link" href="">Email/SMS Templates</a></li>
                    <li><a class="nxl-link" href="">Notification Logs</a></li>
                    <li><a class="nxl-link" href="">Queue Monitor</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 8: ANALYTICS & REPORTS ================== -->
            <li class="nxl-item nxl-caption">
                <label>ANALYTICS & REPORTS</label>
            </li>
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.reports.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                    <span class="nxl-mtext">Performance Reports</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Pass/Fail Rates</a></li>
                    <li><a class="nxl-link" href="">Department KPIs</a></li>
                    <li><a class="nxl-link" href="">NTA Level Analysis</a></li>
                    <li><a class="nxl-link" href="">Graduation Trends</a></li>
                </ul>
            </li>

            <!-- ================== SECTION 9: EXTERNAL COMPLIANCE ================== -->
            <li class="nxl-item nxl-caption">
                <label>EXTERNAL COMPLIANCE</label>
            </li>
            <li class="nxl-item {{ request()->routeIs('dp.academics.exports.nactvet') ? 'active' : '' }}">
                <a href="" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-external-link"></i></span>
                    <span class="nxl-mtext">NACTVET Portal Export</span>
                </a>
            </li>
            <li class="nxl-item {{ request()->routeIs('dp.academics.audit.*') ? 'active' : '' }}">
                <a href="" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-shield"></i></span>
                    <span class="nxl-mtext">Academic Audit Trail</span>
                </a>
            </li>

            <!-- ================== SECTION 10: SYSTEM ADMIN & SAFETY ================== -->
            <li class="nxl-item nxl-caption">
                <label>SYSTEM ADMIN & SAFETY</label>
            </li>

            <!-- Staff Roles & Mapping -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.staff.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-user-plus"></i></span>
                    <span class="nxl-mtext">Staff Roles & Mapping</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Instructor Assignments</a></li>
                    <li><a class="nxl-link" href="">Internal/External Verifiers</a></li>
                    <li><a class="nxl-link" href="">Teaching Load Audit</a></li>
                </ul>
            </li>

            <!-- Conflict Control (Record Locking / Versioning) -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.conflict.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-lock"></i></span>
                    <span class="nxl-mtext">Conflict Control</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Currently Locked Records</a></li>
                    <li><a class="nxl-link" href="">Version History</a></li>
                    <li><a class="nxl-link" href="">Resolve Conflicts</a></li>
                </ul>
            </li>

            <!-- Backup & Recovery -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.backup.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-database"></i></span>
                    <span class="nxl-mtext">Backup & Recovery</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Backup Schedule</a></li>
                    <li><a class="nxl-link" href="">Restore Data</a></li>
                    <li><a class="nxl-link" href="">Backup History</a></li>
                </ul>
            </li>

            <!-- Settings -->
            <li class="nxl-item nxl-hasmenu {{ request()->routeIs('dp.academics.settings.*') ? 'active nxl-trigger' : '' }}">
                <a href="javascript:void(0);" class="nxl-link">
                    <span class="nxl-micon"><i class="feather-settings"></i></span>
                    <span class="nxl-mtext">Settings</span>
                    <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                </a>
                <ul class="nxl-submenu">
                    <li><a class="nxl-link" href="">Grading Scheme</a></li>
                    <li><a class="nxl-link" href="">Notification Templates</a></li>
                    <li><a class="nxl-link" href="">Security & Encryption</a></li>
                </ul>
            </li>

            <!-- Logout -->
            <li class="nxl-item dropdown-divider"></li>
            <li class="nxl-item">
                <a class="nxl-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <span class="nxl-micon"><i class="feather-log-out"></i></span>
                    <span class="nxl-mtext">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<!-- Improved Search Script (Filters submenu and expands parent) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('menuSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase().trim();
            const allMenuItems = document.querySelectorAll('#mainMenu .nxl-item');
            
            if (filter === '') {
                // Reset all items to visible and collapse all submenus
                allMenuItems.forEach(item => {
                    item.style.display = '';
                    if (item.classList.contains('nxl-hasmenu') && !item.classList.contains('active')) {
                        item.classList.remove('nxl-trigger');
                    }
                });
                return;
            }
            
            allMenuItems.forEach(item => {
                const text = item.innerText.toLowerCase();
                if (text.includes(filter)) {
                    item.style.display = '';
                    // Expand parent if this item is inside a submenu
                    let parentHasMenu = item.closest('.nxl-hasmenu');
                    if (parentHasMenu) {
                        parentHasMenu.classList.add('active', 'nxl-trigger');
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

<!-- Hidden logout form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>
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
                               
                                
                            </div>
                            
                            
                            <div class="text-center notifications-footer">
                                <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">Alls Notifications</a>
                            </div>
                        </div>
                    </div>
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

<div class="dropdown nxl-h-item">
    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
        <img src="{{ $avatar }}" 
             alt="{{ $displayName }}" 
             class="img-fluid user-avtar me-0" 
             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&color=7F9CF5&background=EBF4FF&size=128&bold=true';" />
    </a>
    
    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
        <div class="dropdown-header">
            <div class="d-flex align-items-center">
                <img src="{{ $avatar }}" 
                     alt="{{ $displayName }}" 
                     class="img-fluid user-avtar" 
                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&color=7F9CF5&background=EBF4FF&size=128&bold=true';" />
                <div class="ms-2">
                    <h6 class="text-dark mb-0">
                        {{ $displayName }}
                        <span class="badge bg-soft-success text-success ms-1">
                            {{ $displayType }}
                        </span>
                    </h6>
                    <span class="fs-12 fw-medium text-muted">{{ $displayEmail }}</span>
                </div>
            </div>
        </div>
        
        <div class="dropdown-divider"></div>
        
        <a href="" class="dropdown-item">
            <i class="feather-user"></i>
            <span>Profile Details</span>
        </a>
        
        <a href="" class="dropdown-item">
            <i class="feather-settings"></i>
            <span>Account Settings</span>
        </a>
        
        <div class="dropdown-divider"></div>
        
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
            <i class="feather-log-out"></i>
            <span>Logout</span>
        </a>
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
<script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>

<!-- App Init -->
<script src="{{ asset('assets/js/common-init.min.js') }}"></script>
<script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>

<!-- Theme Customizer -->
<script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>

<!-- Load jQuery only if not already loaded -->
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @php
        $jqueryLoaded = true;
    @endphp
@endif

<!-- Custom Mobile Menu Script -->
<script>
// Funga script zako kwenye IIFE ili kuzuia conflicts na jQuery
(function($) {
    'use strict';
    
    $(document).ready(function() {
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
        
        // Optional: Handle submenu toggling on mobile
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
    });
})(window.jQuery);
</script>


<!-- Stack scripts from child views -->
@stack('page-scripts')

</body>
</html>