@extends('layouts.hod')

@section('title', 'Clearance Status')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Clearance Status - 
                        {{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Reg No: {{ $student->registration_number }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Overall Status Banner -->
                    <div class="alert alert-{{ $overallStatus == 'cleared' ? 'success' : 'warning' }} text-center">
                        <h4>
                            @if($overallStatus == 'cleared')
                                <i class="fas fa-check-circle"></i> ALL CLEARED
                            @else
                                <i class="fas fa-exclamation-triangle"></i> PENDING CLEARANCE
                            @endif
                        </h4>
                        <p class="mb-0">
                            @if($overallStatus == 'cleared')
                                This student is fully cleared and ready for graduation/progression.
                            @else
                                Please complete all pending clearance items below.
                            @endif
                        </p>
                    </div>

                    <!-- Clearance Items -->
                    <div class="timeline">
                        @foreach($clearanceItems as $item)
                        <div class="time-label">
                            <span class="bg-{{ $item['status'] == 'cleared' ? 'success' : 'danger' }}">
                                {{ $item['name'] }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-{{ $item['icon'] }} bg-{{ $item['status'] == 'cleared' ? 'green' : 'red' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    @if($item['status'] == 'cleared')
                                        <i class="fas fa-check-circle text-success"></i> Cleared
                                    @else
                                        <i class="fas fa-clock text-warning"></i> Pending
                                    @endif
                                </span>
                                <h3 class="timeline-header">
                                    <a href="#">{{ $item['name'] }}</a>
                                </h3>
                                <div class="timeline-body">
                                    <p>{{ $item['notes'] }}</p>
                                    @if(isset($item['amount']) && $item['amount'] > 0)
                                        <div class="alert alert-danger">
                                            <strong>Outstanding Balance:</strong> 
                                            TZS {{ number_format($item['amount'], 0) }}
                                        </div>
                                    @endif
                                </div>
                                @if($item['status'] != 'cleared')
                                <div class="timeline-footer">
                                    @if($item['name'] == 'Academic Clearance')
                                        <a href="{{ route('hod.students.academic-history', $student->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Results
                                        </a>
                                    @elseif($item['name'] == 'Financial Clearance')
                                        <a href="{{ route('hod.students.profile', $student->id) }}#invoices" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Fees
                                        </a>
                                    @elseif($item['name'] == 'Department Clearance')
                                        <form action="{{ route('hod.students.update-clearance', [$student->id, 'department']) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cleared">
                                            <input type="hidden" name="notes" value="Approved by HOD">
                                            <button type="submit" class="btn btn-success btn-sm"
                                                    onclick="return confirm('Approve department clearance for this student?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('hod.students.update-clearance', [$student->id, strtolower(str_replace(' ', '-', $item['name']))]) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cleared">
                                            <button type="submit" class="btn btn-success btn-sm"
                                                    onclick="return confirm('Mark this item as cleared?')">
                                                <i class="fas fa-check"></i> Mark as Cleared
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>

                    <!-- Clearance Certificate -->
                    @if($overallStatus == 'cleared')
                    <div class="alert alert-success text-center mt-4">
                        <h4><i class="fas fa-certificate"></i> Clearance Certificate</h4>
                        <p>This student has been fully cleared and is eligible for:</p>
                        <ul class="list-unstyled">
                            <li>✓ Registration for next semester/year</li>
                            <li>✓ Graduation ceremony</li>
                            <li>✓ Issuance of certificates</li>
                            <li>✓ Release of academic transcripts</li>
                        </ul>
                        <button class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Clearance Certificate
                        </button>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="{{ route('hod.students.profile', $student->id) }}" 
                           class="btn btn-info">
                            <i class="fas fa-arrow-left"></i> Back to Profile
                        </a>
                        <a href="{{ route('hod.results.transcript', $student->id) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> View Transcript
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ddd;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}
.timeline > div {
    position: relative;
    margin-bottom: 15px;
}
.timeline > div .timeline-item {
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 3px;
    margin-top: 0;
    background: #fff;
    color: #444;
    margin-left: 60px;
    margin-right: 15px;
    padding: 0;
    position: relative;
}
.timeline > div .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}
.timeline > div .timeline-item > .timeline-header {
    margin: 0;
    color: #555;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}
.timeline > div .timeline-item > .timeline-body,
.timeline > div .timeline-item > .timeline-footer {
    padding: 10px;
}
.timeline > div .time-label {
    border-color: #ddd;
    margin-bottom: 15px;
    position: relative;
}
.timeline > div .time-label span {
    background-color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    display: inline-block;
    font-weight: 600;
}
.timeline > div i {
    width: 50px;
    height: 50px;
    background-color: #fff;
    border-radius: 50%;
    text-align: center;
    line-height: 50px;
    font-size: 20px;
    color: #fff;
    position: absolute;
    top: 0;
    left: 18px;
    z-index: 1;
}
.bg-green {
    background-color: #00a65a !important;
}
.bg-red {
    background-color: #dd4b39 !important;
}
@media print {
    .card-tools, .timeline-footer, .text-center, .btn, .sidebar, .navbar {
        display: none !important;
    }
    .timeline-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
@endpush
@endsection