<?php

return [
    'hod' => [
        // ===========================================
        // MENYU ZA KWA WOTE (Common for all departments)
        // ===========================================
        [
            'title' => 'MAIN NAVIGATION',
            'is_header' => true,
        ],
        [
            'title' => 'Dashboard',
            'icon' => 'feather-home',
            'route' => 'hod.dashboard', // Hii inafanya kazi
            'permission' => null,
        ],
        
        // ===========================================
// STUDENTS MANAGEMENT (Kwa wote)
// ===========================================
[
    'title' => 'Students Management',
    'icon' => 'feather-users',
    'route' => '#',
    'permission' => null,
    'children' => [
        [
            'label' => 'All Students',
            'route' => 'hod.students.all',
            'icon' => 'feather-list',
            'permission' => null,
        ],
        [
            'label' => 'Active Students',
            'route' => 'hod.students.active',
            'icon' => 'feather-user-check',
            'permission' => null,
        ],
        [
            'label' => 'Deferred Students',
            'route' => 'hod.students.deferred',
            'icon' => 'feather-pause-circle',
            'permission' => null,
        ],
        [
            'label' => 'Alumni',
            'route' => 'hod.students.alumni',
            'icon' => 'feather-award',
            'permission' => null,
        ],
        [
            'divider' => true,
        ],
        [
            'label' => 'Student Actions',
            'icon' => 'feather-settings',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'View Student Profile',
                    'route' => 'javascript:void(0)',
                    'icon' => 'feather-user',
                    'permission' => null,
                    'onclick' => 'openStudentModal("profile")',
                ],
                [
                    'label' => 'View Academic History',
                    'route' => 'javascript:void(0)',
                    'icon' => 'feather-book-open',
                    'permission' => null,
                    'onclick' => 'openStudentModal("academic")',
                ],
                [
                    'label' => 'Register Courses',
                    'route' => 'javascript:void(0)',
                    'icon' => 'feather-edit',
                    'permission' => null,
                    'onclick' => 'openStudentModal("register")',
                ],
                [
                    'label' => 'Clearance Status',
                    'route' => 'javascript:void(0)',
                    'icon' => 'feather-check-square',
                    'permission' => null,
                    'onclick' => 'openStudentModal("clearance")',
                ],
            ],
        ],
    ],
],
        
       // ===========================================
// PROMOTION MANAGEMENT
// ===========================================
[
    'title' => 'Promotion Management',
    'icon' => 'feather-arrow-up',
    'route' => '#',
    'permission' => null,
    'children' => [
        [
            'label' => 'Promote by Semester',
            'route' => 'hod.promotion.semester',
            'icon' => 'feather-arrow-right-circle',
            'permission' => null,
        ],
        [
            'label' => 'Promote by Level',
            'route' => 'hod.promotion.year',
            'icon' => 'feather-arrow-right',
            'permission' => null,
        ],
        [
            'label' => 'Bulk Promotion',
            'route' => 'hod.promotion.bulk',
            'icon' => 'feather-layers',
            'permission' => null,
        ],
        [
            'label' => 'Promotion History',
            'route' => 'hod.promotion.history',
            'icon' => 'feather-clock',
            'permission' => null,
        ],
    ],
],
        
        // ===========================================
        // RESULTS MANAGEMENT
        // ===========================================
        [
            'title' => 'Results Management',
            'icon' => 'feather-bar-chart-2',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Enter Results',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-edit',
                    'permission' => null,
                ],
                [
                    'label' => 'Moderate Results',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-eye',
                    'permission' => null,
                ],
                [
                    'label' => 'Approve Results',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-check-circle',
                    'permission' => null,
                ],
                [
                    'label' => 'Publish Results',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-send',
                    'permission' => null,
                ],
                [
                    'divider' => true,
                ],
                [
                    'label' => 'GPA/CGPA Report',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-bar-chart',
                    'permission' => null,
                ],
                [
                    'label' => 'Supplementary List',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-repeat',
                    'permission' => null,
                ],
                [
                    'label' => 'Carry Over List',
                    'route' => '#', // Bado hatujatengeneza
                    'icon' => 'feather-alert-circle',
                    'permission' => null,
                ],
                [
                    'label' => 'Transcript',
                    'route' => '#', // INAHITAJI PARAMETER - WEKA '#'
                    'icon' => 'feather-file-text',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
// MENYU ZA KOZI ZA AFYA - ZINAONEKANA KWA PST NA CMT
// ===========================================
[
    'title' => 'Clinical Management',
    'icon' => 'feather-activity',
    'route' => '#',
    'permission' => 'health-department', // Hii inafanya kazi sasa
    'children' => [
        [
            'label' => 'Clinical Rotation',
            'route' => '#',
            'icon' => 'feather-repeat',
            'permission' => 'health-department',
        ],
        [
            'label' => 'Patient Logbook',
            'route' => '#',
            'icon' => 'feather-book',
            'permission' => 'health-department',
        ],
        [
            'label' => 'Supervisor Allocation',
            'route' => '#',
            'icon' => 'feather-user-plus',
            'permission' => 'health-department',
        ],
        [
            'label' => 'Clinical Assessment',
            'route' => '#',
            'icon' => 'feather-check-square',
            'permission' => 'health-department',
        ],
    ],
],

// ===========================================
// MENYU MAALUM KWA PST PEKEE
// ===========================================
[
    'title' => 'Pharmacy Specific',
    'icon' => 'feather-pocket',
    'route' => '#',
    'permission' => 'pharmacy-department', // Tu kwa PST (ID 12)
    'children' => [
        [
            'label' => 'Drug Inventory',
            'route' => '#',
            'icon' => 'feather-package',
            'permission' => 'pharmacy-department',
        ],
        [
            'label' => 'Prescription Tracking',
            'route' => '#',
            'icon' => 'feather-file-text',
            'permission' => 'pharmacy-department',
        ],
    ],
],

// ===========================================
// MENYU MAALUM KWA CMT PEKEE
// ===========================================
[
    'title' => 'Clinical Medicine Specific',
    'icon' => 'feather-heart',
    'route' => '#',
    'permission' => 'clinical-department', // Tu kwa CMT (ID 13)
    'children' => [
        [
            'label' => 'Patient Records',
            'route' => '#',
            'icon' => 'feather-file',
            'permission' => 'clinical-department',
        ],
        [
            'label' => 'Ward Rounds',
            'route' => '#',
            'icon' => 'feather-activity',
            'permission' => 'clinical-department',
        ],
    ],
],
        // ===========================================
        // ACADEMIC MANAGEMENT (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Academic Management',
            'icon' => 'feather-book',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Programs',
                    'route' => '#',
                    'icon' => 'feather-book',
                    'permission' => null,
                ],
                [
                    'label' => 'Courses',
                    'route' => '#',
                    'icon' => 'feather-book-open',
                    'permission' => null,
                ],
                [
                    'label' => 'Curriculum',
                    'route' => '#',
                    'icon' => 'feather-layers',
                    'permission' => null,
                ],
                [
                    'label' => 'Semester Setup',
                    'route' => '#',
                    'icon' => 'feather-calendar',
                    'permission' => null,
                ],
                [
                    'label' => 'Timetable',
                    'route' => '#',
                    'icon' => 'feather-clock',
                    'permission' => null,
                ],
                [
                    'label' => 'Exam Setup',
                    'route' => '#',
                    'icon' => 'feather-edit-3',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // STAFF MANAGEMENT (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Staff Management',
            'icon' => 'feather-users',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'All Staff',
                    'route' => '#',
                    'icon' => 'feather-list',
                    'permission' => null,
                ],
                [
                    'label' => 'Lecturers',
                    'route' => '#',
                    'icon' => 'feather-user-check',
                    'permission' => null,
                ],
                [
                    'label' => 'Teaching Load',
                    'route' => '#',
                    'icon' => 'feather-clock',
                    'permission' => null,
                ],
                [
                    'label' => 'Course Allocation',
                    'route' => '#',
                    'icon' => 'feather-book-open',
                    'permission' => null,
                ],
                [
                    'label' => 'Leave Requests',
                    'route' => '#',
                    'icon' => 'feather-calendar',
                    'permission' => null,
                ],
                [
                    'label' => 'Staff Performance',
                    'route' => '#',
                    'icon' => 'feather-trending-up',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // FINANCE MANAGEMENT (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Finance',
            'icon' => 'feather-dollar-sign',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Fee Status',
                    'route' => '#',
                    'icon' => 'feather-credit-card',
                    'permission' => null,
                ],
                [
                    'label' => 'Outstanding Balances',
                    'route' => '#',
                    'icon' => 'feather-clock',
                    'permission' => null,
                ],
                [
                    'label' => 'Financial Clearance',
                    'route' => '#',
                    'icon' => 'feather-check-square',
                    'permission' => null,
                ],
                [
                    'label' => 'Department Budget',
                    'route' => '#',
                    'icon' => 'feather-pie-chart',
                    'permission' => null,
                ],
                [
                    'label' => 'Requisition Approval',
                    'route' => '#',
                    'icon' => 'feather-check-circle',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // REPORTS (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Reports',
            'icon' => 'feather-file-text',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Enrollment Report',
                    'route' => '#',
                    'icon' => 'feather-users',
                    'permission' => null,
                ],
                [
                    'label' => 'Performance Report',
                    'route' => '#',
                    'icon' => 'feather-bar-chart-2',
                    'permission' => null,
                ],
                [
                    'label' => 'Graduation List',
                    'route' => '#',
                    'icon' => 'feather-award',
                    'permission' => null,
                ],
                [
                    'label' => 'Promotion List',
                    'route' => '#',
                    'icon' => 'feather-arrow-up',
                    'permission' => null,
                ],
                [
                    'label' => 'Financial Summary',
                    'route' => '#',
                    'icon' => 'feather-dollar-sign',
                    'permission' => null,
                ],
                [
                    'label' => 'Staff Workload',
                    'route' => '#',
                    'icon' => 'feather-users',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // APPROVALS (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Approvals',
            'icon' => 'feather-check-circle',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Result Approval',
                    'route' => '#',
                    'icon' => 'feather-bar-chart-2',
                    'permission' => null,
                ],
                [
                    'label' => 'Promotion Approval',
                    'route' => '#',
                    'icon' => 'feather-arrow-up',
                    'permission' => null,
                ],
                [
                    'label' => 'Budget Approval',
                    'route' => '#',
                    'icon' => 'feather-pie-chart',
                    'permission' => null,
                ],
                [
                    'label' => 'Leave Approval',
                    'route' => '#',
                    'icon' => 'feather-calendar',
                    'permission' => null,
                ],
                [
                    'label' => 'Clearance Approval',
                    'route' => '#',
                    'icon' => 'feather-check-square',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // ASSETS & RESOURCES (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Assets',
            'icon' => 'feather-box',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Department Assets',
                    'route' => '#',
                    'icon' => 'feather-list',
                    'permission' => null,
                ],
                [
                    'label' => 'Lab Equipment',
                    'route' => '#',
                    'icon' => 'feather-tool',
                    'permission' => null,
                ],
                [
                    'label' => 'Asset Allocation',
                    'route' => '#',
                    'icon' => 'feather-share-2',
                    'permission' => null,
                ],
                [
                    'label' => 'Maintenance Log',
                    'route' => '#',
                    'icon' => 'feather-tool',
                    'permission' => null,
                ],
            ],
        ],
        
        // ===========================================
        // SETTINGS (ZOTE ZIWE '#')
        // ===========================================
        [
            'title' => 'Settings',
            'icon' => 'feather-settings',
            'route' => '#',
            'permission' => null,
            'children' => [
                [
                    'label' => 'Department Profile',
                    'route' => '#',
                    'icon' => 'feather-info',
                    'permission' => null,
                ],
                [
                    'label' => 'Academic Calendar',
                    'route' => '#',
                    'icon' => 'feather-calendar',
                    'permission' => null,
                ],
                [
                    'label' => 'Grading System',
                    'route' => '#',
                    'icon' => 'feather-percent',
                    'permission' => null,
                ],
                [
                    'label' => 'Notification Settings',
                    'route' => '#',
                    'icon' => 'feather-bell',
                    'permission' => null,
                ],
                [
                    'label' => 'My Profile',
                    'route' => 'hod.profile', // Hii inafanya kazi
                    'icon' => 'feather-user',
                    'permission' => null,
                ],
            ],
        ],
    ],
];