@extends('layouts.students')

@section('content')
<style>
    /* Minimalist Dashboard - Kama picha yako */
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Header - Current Year, Last Login, Today */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
        padding: 12px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #eef2f6;
    }
    
    .header-item {
        color: #555;
        font-size: 13px;
    }
    
    .header-item strong {
        color: #1a1a2e;
        font-weight: 600;
    }
    
    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 25px 30px;
        margin-bottom: 30px;
        color: white;
    }
    
    .welcome-banner h2 {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 12px;
    }
    
    .banner-stats p {
        margin: 5px 0;
        opacity: 0.95;
        font-size: 14px;
    }
    
    .banner-stats strong {
        font-weight: 600;
    }
    
    /* Two Column Layout */
    .two-columns {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    
    /* Left Column - Profile */
    .profile-column {
        flex: 1;
        min-width: 280px;
    }
    
    /* Right Column - Timetable */
    .timetable-column {
        flex: 2;
        min-width: 300px;
    }
    
    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #eef2f6;
    }
    
    .profile-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    
    .profile-row:last-child {
        border-bottom: none;
    }
    
    .profile-label {
        font-size: 13px;
        color: #666;
    }
    
    .profile-value {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a2e;
    }
    
    /* Timetable Card */
    .timetable-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #eef2f6;
    }
    
    .timetable-title {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
        display: inline-block;
    }
    
    /* Timetable Table */
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .timetable-table th {
        text-align: left;
        padding: 12px 8px;
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        color: #1a1a2e;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .timetable-table td {
        padding: 12px 8px;
        font-size: 13px;
        color: #555;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .section-row td {
        background: #f8f9fa;
        font-weight: 600;
        color: #667eea;
        border-bottom: 1px solid #e0e0e0;
    }
    
    /* Footer */
    .footer {
        text-align: center;
        padding: 20px;
        margin-top: 30px;
        border-top: 1px solid #e0e0e0;
        font-size: 12px;
        color: #999;
    }
    
    /* ============================================
       RESPONSIVE DESIGN
       ============================================ */
    
    /* Screen kubwa (Desktop) */
    @media (min-width: 769px) {
        .welcome-banner {
            text-align: left;
        }
        
        .banner-stats {
            text-align: left;
        }
        
        .two-columns {
            flex-direction: row;
        }
    }
    
    /* Screen ndogo (Simu) - Text LEFT */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 15px;
    }
    
    /* Header - lines 2 */
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 12px 16px;
    }
    
    .header-item:first-child {
        width: 100%;
    }
    
    .header-item:nth-child(2),
    .header-item:nth-child(3) {
        display: inline-block;
        width: auto;
    }
    
    .header-item:nth-child(2) {
        margin-right: 15px;
    }
    
    /* Welcome Banner - TEXT LEFT (kushoto) */
    .welcome-banner {
        text-align: left;
        padding: 20px;
    }
    
    .welcome-banner h2 {
        font-size: 18px;
        text-align: left;
    }
    
    .banner-stats {
        text-align: left;
    }
    
    .banner-stats p {
        text-align: left;
        margin: 5px 0;
    }
    
    /* Two Columns - Stacked */
    .two-columns {
        flex-direction: column;
    }
    
    /* Timetable */
    .timetable-table th,
    .timetable-table td {
        padding: 8px 5px;
        font-size: 11px;
    }
}
    /* Screen ndogo sana (<=480px) */
    @media (max-width: 480px) {
        .dashboard-header {
            padding: 10px 12px;
        }
        
        .header-item {
            font-size: 11px;
        }
        
        .welcome-banner {
            padding: 15px;
        }
        
        .welcome-banner h2 {
            font-size: 16px;
        }
        
        .banner-stats p {
            font-size: 12px;
        }
        
        .profile-row {
            flex-direction: column;
            gap: 5px;
        }
        
        .profile-label {
            width: 100%;
        }
        
        .profile-value {
            width: 100%;
        }
    }
    
    /* Print */
    @media print {
        .footer {
            display: none;
        }
        
        .welcome-banner {
            background: #667eea;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="dashboard-container">
    
    <!-- Header: Current Year, Last Login, Today -->
    <div class="dashboard-header">
        <div class="header-item"><strong>Current Year:</strong> {{ $student->academicYear->name ?? 'N/A' }}</div>
        <div class="header-item"><strong>Last Login:</strong> 
         <strong>Today:</strong> {{ now()->format('d M, Y') }}</div>
    </div>
    
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h2>Welcome to Student Information System</h2>
        <div class="banner-stats">
            @php
                $ntaLevel = $student->current_level ?? 1;
                
                if ($ntaLevel <= 4) {
                    $yearOfStudy = '1st Year';
                } elseif ($ntaLevel == 5) {
                    $yearOfStudy = '2nd Year';
                } else {
                    $yearOfStudy = '3rd Year';
                }
            @endphp
            <p><strong>Year of Study:</strong> {{ $yearOfStudy }}</p>
            <p><strong>Stream:</strong> {{ $student->stream ?? '' }}</p>
            <p><strong>Student Status:</strong> {{ $student->status === 'active' ? 'Continue' : ucfirst($student->status) }}</p>
        </div>
    </div>
    
    <!-- Two Columns: Profile + Timetable -->
    <div class="two-columns">
        
      
        
        <!-- RIGHT COLUMN: TIMETABLE -->
        <div class="timetable-column">
            <div class="timetable-card">
                <div class="timetable-title">Latest Timetable</div>
                
                <div class="table-responsive">
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 15%;">Code</th>
                                <th style="width: 45%;">Module Name</th>
                                <th style="width: 35%;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

          <!-- LEFT COLUMN: PROFILE -->
        <div class="profile-column">
            <div class="profile-card">
                <div class="profile-row">
                    <span class="profile-label">Entry Year</span>
                    <span class="profile-value">{{ $student->academicYear->name ?? 'N/A' }}</span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Intake</span>
                    <span class="profile-value">{{ $student->intake ?? 'March' }}</span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Session</span>
                    <span class="profile-value">{{ ucfirst(str_replace('_', ' ', $student->study_mode ?? 'Full Time')) }}</span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Program</span>
                    <span class="profile-value">{{ $student->programme->name ?? 'N/A' }}</span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Department</span>
                    <span class="profile-value">{{ $student->programme->department->name ?? $student->programme->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Footer -->
    <div class="footer">
        Copyright © {{ date('Y') }} <strong>St. Maxillian College</strong>. All rights reserved.
    </div>
    
</div>
@endsection