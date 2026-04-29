{{-- resources/views/superadmin/results/statistics.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Results Statistics')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Results Statistics
            </h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Grade Distribution</div>
                        <div class="card-body">
                            <canvas id="gradeChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Pass/Fail Rate</div>
                        <div class="card-body">
                            <canvas id="passFailChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Top Performing Modules</div>
                        <div class="card-body">
                            <canvas id="moduleChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('scripts')
<script>
let gradeChart, passFailChart, moduleChart;

$(document).ready(function() {
    loadStatistics();
});

function loadStatistics() {
    $.get('/superadmin/results/api/statistics', function(response) {
        if (response.success) {
            renderGradeChart(response.data.gradeDistribution);
            renderPassFailChart(response.data.passFailRate);
            renderModuleChart(response.data.topModules);
        }
    });
}

function renderGradeChart(data) {
    let ctx = document.getElementById('gradeChart').getContext('2d');
    if (gradeChart) gradeChart.destroy();
    
    gradeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(data),
            datasets: [{
                label: 'Number of Students',
                data: Object.values(data),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
}

function renderPassFailChart(data) {
    let ctx = document.getElementById('passFailChart').getContext('2d');
    if (passFailChart) passFailChart.destroy();
    
    passFailChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pass', 'Fail'],
            datasets: [{
                data: [data.pass, data.fail],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        }
    });
}

function renderModuleChart(data) {
    let ctx = document.getElementById('moduleChart').getContext('2d');
    if (moduleChart) moduleChart.destroy();
    
    moduleChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.map(m => m.module_name),
            datasets: [{
                label: 'Average Score',
                data: data.map(m => m.avg_score),
                backgroundColor: 'rgba(75, 192, 192, 0.5)'
            }]
        }
    });
}

function refreshData() {
    loadStatistics();
    toastr.info('Data refreshed');
}
</script>
@endpush