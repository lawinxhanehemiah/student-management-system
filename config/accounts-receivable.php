<?php
// config/accounts-receivable.php

return [
    'aging' => [
        'buckets' => [
            'current' => ['min' => 0, 'max' => 0, 'color' => '#28a745'],
            '1_30_days' => ['min' => 1, 'max' => 30, 'color' => '#ffc107'],
            '31_60_days' => ['min' => 31, 'max' => 60, 'color' => '#fd7e14'],
            '61_90_days' => ['min' => 61, 'max' => 90, 'color' => '#dc3545'],
            '90_plus_days' => ['min' => 91, 'max' => null, 'color' => '#6c757d'],
        ],
        'provision_rates' => [
            'current' => 1,
            '1_30_days' => 5,
            '31_60_days' => 10,
            '61_90_days' => 25,
            '90_plus_days' => 50,
        ],
        'reminder_intervals' => [
            'first_reminder' => 15,
            'second_reminder' => 30,
            'third_reminder' => 45,
            'final_notice' => 60,
        ],
    ],
    
    'credit_notes' => [
        'expiry_days' => 365,
        'auto_apply' => true,
        'max_per_invoice' => 10,
    ],
    
    'refunds' => [
        'methods' => ['bank_transfer', 'mpesa', 'cash', 'cheque'],
        'require_approval' => true,
        'approval_levels' => ['finance_officer', 'finance_manager'],
        'max_amount_no_approval' => 100000, // TZS
    ],
    
    'reports' => [
        'export_formats' => ['pdf', 'excel', 'csv'],
        'include_breakdowns' => true,
        'default_period' => 'month',
    ],
];