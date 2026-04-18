@extends('layouts.app')

@section('title', 'Dashboard - Application Portal')

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="welcome-subtitle">
                    @if($stats['total'] == 0)
                        Start your application journey today. Create your first application!
                    @elseif($pendingDrafts > 0)
                        You have {{ $pendingDrafts }} draft application(s). Continue where you left off.
                    @else
                        Track your {{ $stats['total'] }} application(s) and stay updated on their status.
                    @endif
                </p>
                
                <div class="welcome-actions mt-4">
                    <a href="{{ route('application.start') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Start New Application
                    </a>
                    
                    @if($pendingDrafts > 0)
                    <a href="{{ route('application.start') }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-edit me-2"></i>Continue Draft
                    </a>
                    @endif
                    
                    <a href="#applications" class="btn btn-info btn-lg">
                        <i class="fas fa-list me-2"></i>View Applications
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="academic-info-card">
                    <div class="info-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="info-content">
                        <h4>Academic Year</h4>
                        <h3 class="mb-0">{{ $activeYear->name ?? '2024/2025' }}</h3>
                        <small class="text-muted">Active Admission Cycle</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-total">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Applications</p>
                </div>
                <div class="stat-trend">
                    <span class="text-success">
                        <i class="fas fa-chart-line me-1"></i>
                        {{ $stats['this_month'] }} this month
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-submitted">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['submitted'] }}</h3>
                    <p>Submitted</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-draft">
                <div class="stat-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['draft'] }}</h3>
                    <p>Drafts</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-accepted">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ $stats['accepted'] }}</h3>
                    <p>Accepted</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Status Distribution -->
        <div class="col-lg-8 mb-4">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Application Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="statusChart" height="250"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="status-legend">
                                @php
                                    $statusData = [
                                        ['status' => 'Submitted', 'count' => $stats['submitted'], 'color' => '#3498db', 'icon' => 'paper-plane'],
                                        ['status' => 'Draft', 'count' => $stats['draft'], 'color' => '#f39c12', 'icon' => 'edit'],
                                        ['status' => 'Under Review', 'count' => $stats['under_review'], 'color' => '#9b59b6', 'icon' => 'search'],
                                        ['status' => 'Accepted', 'count' => $stats['accepted'], 'color' => '#27ae60', 'icon' => 'check-circle'],
                                        ['status' => 'Rejected', 'count' => $stats['rejected'], 'color' => '#e74c3c', 'icon' => 'times-circle'],
                                    ];
                                @endphp
                                
                                @foreach($statusData as $item)
                                @if($item['count'] > 0)
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: {{ $item['color'] }}"></div>
                                    <div class="legend-icon">
                                        <i class="fas fa-{{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="legend-text">
                                        <strong>{{ $item['status'] }}</strong>
                                        <span>{{ $item['count'] }} application(s)</span>
                                    </div>
                                    <div class="legend-percentage">
                                        {{ number_format(($item['count'] / $stats['total']) * 100, 1) }}%
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="{{ route('application.start') }}" class="quick-action-item">
                            <div class="action-icon bg-primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-text">
                                <h6>New Application</h6>
                                <small>Start fresh application</small>
                            </div>
                        </a>
                        
                        @if($pendingDrafts > 0)
                        <a href="{{ route('application.start') }}" class="quick-action-item">
                            <div class="action-icon bg-warning">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="action-text">
                                <h6>Continue Draft</h6>
                                <small>{{ $pendingDrafts }} pending</small>
                            </div>
                        </a>
                        @endif
                        
                        <a href="{{ route('profile.edit') }}" class="quick-action-item">
                            <div class="action-icon bg-info">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="action-text">
                                <h6>Update Profile</h6>
                                <small>Personal information</small>
                            </div>
                        </a>
                        
                        <a href="{{ asset('documents/guidelines.pdf') }}" target="_blank" class="quick-action-item">
                            <div class="action-icon bg-success">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="action-text">
                                <h6>Guidelines</h6>
                                <small>Application rules</small>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action-item" onclick="window.print()">
                            <div class="action-icon bg-secondary">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="action-text">
                                <h6>Print</h6>
                                <small>Print documents</small>
                            </div>
                        </a>
                        
                        <a href="#" class="quick-action-item" data-bs-toggle="modal" data-bs-target="#helpModal">
                            <div class="action-icon bg-danger">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="action-text">
                                <h6>Help</h6>
                                <small>Need assistance?</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="dashboard-card mb-4" id="applications">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>Recent Applications
            </h5>
            <div>
                <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($recentApplications->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h4>No Applications Yet</h4>
                    <p class="text-muted mb-4">You haven't created any applications yet.</p>
                    <a href="{{ route('application.start') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Start Your First Application
                    </a>
                </div>
            @else
                <div class="applications-timeline">
                    @foreach($recentApplications as $application)
                    <div class="timeline-item">
                        <div class="timeline-icon {{ $application->status }}">
                            <i class="fas fa-{{ 
                                $application->status == 'draft' ? 'edit' : 
                                ($application->status == 'submitted' ? 'paper-plane' : 
                                ($application->status == 'accepted' ? 'check-circle' : 'file-alt'))
                            }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <h6 class="mb-1">
                                    {{ $application->application_number }}
                                    @if($application->is_free_application)
                                        <span class="badge bg-success ms-2">Free</span>
                                    @endif
                                </h6>
                                <span class="timeline-time">
                                    {{ $application->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="mb-1">
                                <strong>Program:</strong> 
                                {{ optional($application->programChoice->program)->name ?? 'Not Selected' }}
                            </p>
                            <div class="timeline-footer">
                                <span class="badge status-{{ $application->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                                <div class="timeline-actions">
                                    <a href="{{ route('application.view', $application->id) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($application->status == 'draft')
                                    <a href="{{ route('application.start') }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Application Progress -->
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-tasks me-2"></i>Application Progress
            </h5>
        </div>
        <div class="card-body">
            <div class="progress-info mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Application Completion Rate</span>
                    <span>{{ number_format(($stats['submitted'] / max($stats['total'], 1)) * 100, 1) }}%</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" 
                         style="width: {{ ($stats['submitted'] / max($stats['total'], 1)) * 100 }}%">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="progress-card">
                        <div class="progress-card-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div class="progress-card-content">
                            <h5>Success Rate</h5>
                            <h2>{{ $stats['total'] > 0 ? number_format(($stats['accepted'] / $stats['total']) * 100, 1) : 0 }}%</h2>
                            <small>{{ $stats['accepted'] }} out of {{ $stats['total'] }} accepted</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="progress-card">
                        <div class="progress-card-icon">
                            <i class="fas fa-clock text-warning"></i>
                        </div>
                        <div class="progress-card-content">
                            <h5>Pending Review</h5>
                            <h2>{{ $stats['under_review'] }}</h2>
                            <small>Applications under review</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Need Help?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h4>How can we help you?</h4>
                </div>
                
                <div class="help-options">
                    <a href="mailto:admissions@college.ac.tz" class="help-option">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h6>Email Support</h6>
                            <small>admissions@college.ac.tz</small>
                        </div>
                    </a>
                    
                    <a href="tel:+255123456789" class="help-option">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h6>Call Support</h6>
                            <small>+255 123 456 789</small>
                        </div>
                    </a>
                    
                    <a href="{{ asset('documents/faq.pdf') }}" target="_blank" class="help-option">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <h6>FAQ Document</h6>
                            <small>Frequently Asked Questions</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Charts -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Submitted', 'Draft', 'Under Review', 'Accepted', 'Rejected'],
            datasets: [{
                data: [
                    {{ $stats['submitted'] }},
                    {{ $stats['draft'] }},
                    {{ $stats['under_review'] }},
                    {{ $stats['accepted'] }},
                    {{ $stats['rejected'] }}
                ],
                backgroundColor: [
                    '#3498db', // Submitted - Blue
                    '#f39c12', // Draft - Orange
                    '#9b59b6', // Under Review - Purple
                    '#27ae60', // Accepted - Green
                    '#e74c3c'  // Rejected - Red
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + ' applications';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Fetch timeline data
    fetch('/applicant/dashboard/timeline')
        .then(response => response.json())
        .then(data => {
            // You can use this data to populate a timeline if needed
            console.log('Timeline data:', data);
        });
});

// Quick action functions
function startNewApplication() {
    window.location.href = '{{ route("application.start") }}';
}

function continueDraft() {
    window.location.href = '{{ route("application.start") }}';
}

function downloadGuidelines() {
    window.open('{{ asset("documents/guidelines.pdf") }}', '_blank');
}
</script>
@endpush

<style>
/* Dashboard Custom Styles */
.dashboard-container {
    padding: 20px;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
}

.welcome-title {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.welcome-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.welcome-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

/* Academic Info Card */
.academic-info-card {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.academic-info-card .info-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.academic-info-card .info-content h4 {
    font-size: 0.9rem;
    margin-bottom: 5px;
    opacity: 0.8;
}

.academic-info-card .info-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
}

/* Statistics Cards */
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-total .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-submitted .stat-icon { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-draft .stat-icon { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-accepted .stat-icon { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.stat-trend {
    margin-left: auto;
    font-size: 0.8rem;
}

/* Dashboard Cards */
.dashboard-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    margin-bottom: 20px;
    overflow: hidden;
}

.dashboard-card .card-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.dashboard-card .card-body {
    padding: 20px;
}

/* Status Legend */
.status-legend {
    padding: 10px 0;
}

.legend-item {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 10px;
}

.legend-icon {
    width: 30px;
    text-align: center;
    margin-right: 10px;
    color: #7f8c8d;
}

.legend-text {
    flex: 1;
}

.legend-text strong {
    display: block;
    font-size: 0.9rem;
    color: #2c3e50;
}

.legend-text span {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.legend-percentage {
    font-weight: 700;
    color: #2c3e50;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.quick-action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 20px 10px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.quick-action-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    text-decoration: none;
    color: #333;
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 10px;
}

.action-text h6 {
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.action-text small {
    font-size: 0.75rem;
    color: #7f8c8d;
}

/* Timeline */
.applications-timeline {
    position: relative;
    padding-left: 30px;
}

.applications-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
}

.timeline-icon.draft { background: #f39c12; }
.timeline-icon.submitted { background: #3498db; }
.timeline-icon.accepted { background: #27ae60; }
.timeline-icon.under_review { background: #9b59b6; }
.timeline-icon.rejected { background: #e74c3c; }

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.timeline-header h6 {
    margin: 0;
    font-size: 1rem;
}

.timeline-time {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.timeline-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-draft { background: #fff3cd; color: #856404; }
.status-submitted { background: #d1ecf1; color: #0c5460; }
.status-accepted { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }
.status-under_review { background: #e2d9f3; color: #46376e; }

.timeline-actions {
    display: flex;
    gap: 5px;
}

/* Progress Cards */
.progress-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.progress-card-icon {
    font-size: 2.5rem;
}

.progress-card-content h5 {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.progress-card-content h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #2c3e50;
}

/* Help Options */
.help-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.help-option {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.help-option:hover {
    background: #e9ecef;
    text-decoration: none;
    color: #333;
}

.help-option i {
    font-size: 1.5rem;
    margin-right: 15px;
    color: #3498db;
}

.help-option h6 {
    margin: 0;
    font-size: 1rem;
}

.help-option small {
    color: #7f8c8d;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-actions {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .welcome-actions .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .applications-timeline {
        padding-left: 20px;
    }
    
    .applications-timeline::before {
        left: 10px;
    }
    
    .timeline-icon {
        left: -20px;
    }
    
    .timeline-header {
        flex-direction: column;
    }
    
    .timeline-time {
        margin-top: 5px;
    }
    
    .timeline-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>
@endsection