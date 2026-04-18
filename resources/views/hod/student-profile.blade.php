@extends('layouts.hod')

@section('title', 'Student Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <!-- Student Info Card (same as before) -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        @if($student->user->profile_photo)
                            <img class="profile-user-img img-fluid img-circle" 
                                 src="{{ asset('storage/'.$student->user->profile_photo) }}" 
                                 alt="User profile picture">
                        @else
                            <div class="profile-user-img img-fluid img-circle bg-secondary d-flex align-items-center justify-content-center" 
                                 style="width: 100px; height: 100px; margin: 0 auto;">
                                <span style="font-size: 40px;">
                                    {{ strtoupper(substr($student->user->first_name ?? 'S', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <h3 class="profile-username text-center">
                        {{ $student->user->first_name ?? '' }} 
                        {{ $student->user->middle_name ?? '' }} 
                        {{ $student->user->last_name ?? '' }}
                    </h3>

                    <p class="text-muted text-center">{{ $student->programme->name ?? 'Programme' }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Registration Number</b> 
                            <a class="float-right">{{ $student->registration_number }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Intake</b> 
                            <a class="float-right">{{ $student->intake }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Current Level</b> 
                            <a class="float-right">Year {{ $student->current_level }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Current Semester</b> 
                            <a class="float-right">Semester {{ $student->current_semester }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Study Mode</b> 
                            <a class="float-right">{{ ucfirst(str_replace('_', ' ', $student->study_mode)) }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Academic Year</b> 
                            <a class="float-right">{{ $student->academicYear->name ?? 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Status</b> 
                            <a class="float-right">
                                @if($student->status == 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($student->status == 'graduated')
                                    <span class="badge badge-primary">Graduated</span>
                                @else
                                    <span class="badge badge-warning">Inactive</span>
                                @endif
                            </a>
                        </li>
                        @if(isset($totalBalance) && $totalBalance > 0)
                        <li class="list-group-item">
                            <b>Fee Balance</b> 
                            <a class="float-right text-danger">
                                TZS {{ number_format($totalBalance, 0) }}
                            </a>
                        </li>
                        @endif
                    </ul>

                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('hod.students.academic-history', $student->id) }}" 
                               class="btn btn-info btn-block">
                                <i class="fas fa-history"></i> Academic History
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('hod.students.clearance', $student->id) }}" 
                               class="btn btn-primary btn-block">
                                <i class="fas fa-check-circle"></i> Clearance
                            </a>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('hod.students.register-courses', $student->id) }}" 
                               class="btn btn-success btn-block">
                                <i class="fas fa-edit"></i> Register Courses
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills" id="studentProfileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" id="personal-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="true">
                                <i class="fas fa-user"></i> Personal Information
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="courses-tab" data-toggle="tab" href="#courses" role="tab" aria-controls="courses" aria-selected="false">
                                <i class="fas fa-book"></i> Current Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="invoices-tab" data-toggle="tab" href="#invoices" role="tab" aria-controls="invoices" aria-selected="false">
                                <i class="fas fa-file-invoice-dollar"></i> Fee Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="guardian-tab" data-toggle="tab" href="#guardian" role="tab" aria-controls="guardian" aria-selected="false">
                                <i class="fas fa-users"></i> Guardian Info
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="studentProfileTabContent">
                        <!-- Personal Information Tab (same as before) -->
                        <div class="tab-pane fade" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                                    <p class="text-muted">{{ $student->user->email ?? 'N/A' }}</p>
                                    <hr>
                                    
                                    <strong><i class="fas fa-phone mr-1"></i> Phone</strong>
                                    <p class="text-muted">{{ $student->user->phone ?? 'N/A' }}</p>
                                    <hr>
                                    
                                    <strong><i class="fas fa-venus-mars mr-1"></i> Gender</strong>
                                    <p class="text-muted">{{ ucfirst($student->user->gender ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar-alt mr-1"></i> Date of Birth</strong>
                                    <p class="text-muted">{{ $student->user->date_of_birth ? \Carbon\Carbon::parse($student->user->date_of_birth)->format('d M, Y') : 'N/A' }}</p>
                                    <hr>
                                    
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                                    <p class="text-muted">{{ $student->user->address ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Courses Tab (same as before) -->
                        <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        葩
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Credits</th>
                                            <th>Status</th>
                                        </thead>
                                    <tbody>
                                        @forelse($currentRegistrations ?? [] as $registration)
                                        	<tr>
                                            	<td><strong>{{ $registration->course->code ?? 'N/A' }}</strong></td>
                                            	<td>{{ $registration->course->name ?? 'N/A' }}</td>
                                            	<td>{{ $registration->course->credit_hours ?? 3 }}</td>
                                            	<td>
                                                    <span class="badge badge-success">{{ ucfirst($registration->status) }}</span>
                                                </td>
                                        	</tr>
                                        @empty
                                        	<tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="fas fa-info-circle"></i> 
                                                        No courses registered for current semester
                                                    </div>
                                                </td>
                                        	</tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if(isset($currentRegistrations) && $currentRegistrations->isEmpty())
                            <div class="text-center mt-3">
                                <a href="{{ route('hod.students.register-courses', $student->id) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-edit"></i> Register Courses Now
                                </a>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Invoices Tab with Academic Year Filter -->
                        <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                            <!-- Academic Year Filter -->
                            <div class="row mb-4">
                                <div class="col-md-6 offset-md-3">
                                    <div class="card card-outline card-primary">
                                        <div class="card-body">
                                            <form method="GET" action="{{ route('hod.students.profile', $student->id) }}" id="yearFilterForm">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        <i class="fas fa-calendar-alt"></i> Academic Year
                                                    </label>
                                                    <select name="academic_year_id" class="form-control" id="academicYearSelect">
                                                        <option value="">All Academic Years</option>
                                                        @foreach($academicYears ?? [] as $year)
                                                            <option value="{{ $year->id }}" 
                                                                {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                                {{ $year->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i> 
                                                        Select academic year to filter statement
                                                    </small>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            <!-- Statement Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Academic Year</th>
                                            <th>Control Number</th>
                                            <th>Receipt</th>
                                            <th>Fee Type</th>
                                            <th class="text-right">Debit</th>
                                            <th class="text-right">First Installment</th>
                                            <th class="text-right">Credit</th>
                                            <th class="text-right">Balance</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions ?? [] as $index => $transaction)
                                        <tr class="{{ $transaction['type'] == 'invoice' ? 'table-light' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $transaction['academic_year'] }}</td>
                                            <td>
                                                <strong>{{ $transaction['control_number'] }}</strong>
                                            </td>
                                            <td>
                                                @if($transaction['receipt'] == 'INVOICE')
                                                    <span class="badge badge-primary">INVOICE</span>
                                                @else
                                                    <span class="badge badge-success">PAYMENT</span>
                                                @endif
                                            </td>
                                            <td>{{ $transaction['fee_type'] }}</td>
                                            <td class="text-right text-danger">
                                                @if($transaction['debit'] > 0)
                                                    TZS {{ number_format($transaction['debit'], 0) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(isset($transaction['installment']) && $transaction['installment'] > 0)
                                                    TZS {{ number_format($transaction['installment'], 0) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-right text-success">
                                                @if($transaction['credit'] > 0)
                                                    TZS {{ number_format($transaction['credit'], 0) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                TZS {{ number_format($transaction['running_balance'], 0) }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d M, Y H:i:s') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle"></i> 
                                                    No transactions found for the selected academic year
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if(isset($transactions) && count($transactions) > 0)
                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="5" class="text-right"><strong>TOTALS</strong></td>
                                            <td class="text-right"><strong>TZS {{ number_format($totalDebit ?? 0, 0) }}</strong></td>
                                            <td class="text-right"><strong>-</strong></td>
                                            <td class="text-right"><strong>TZS {{ number_format($totalCredit ?? 0, 0) }}</strong></td>
                                            <td class="text-right">
                                                <strong class="{{ ($closingBalance ?? 0) < 0 ? 'text-warning' : (($closingBalance ?? 0) > 0 ? 'text-danger' : 'text-success') }}">
                                                    TZS {{ number_format($closingBalance ?? 0, 0) }}
                                                </strong>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>

                            
                        </div>
                        
                        <!-- Guardian Information Tab (same as before) -->
                        <div class="tab-pane fade" id="guardian" role="tabpanel" aria-labelledby="guardian-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-user mr-1"></i> Guardian Name</strong>
                                    <p class="text-muted">{{ $student->guardian_name ?? 'N/A' }}</p>
                                    <hr>
                                    
                                    <strong><i class="fas fa-phone mr-1"></i> Guardian Phone</strong>
                                    <p class="text-muted">{{ $student->guardian_phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-users mr-1"></i> Relationship</strong>
                                    <p class="text-muted">{{ $student->guardian_relationship ?? 'Parent/Guardian' }}</p>
                                    <hr>
                                    
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Guardian Address</strong>
                                    <p class="text-muted">{{ $student->guardian_address ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if($student->sponsorship_type)
                            <div class="alert alert-info mt-3">
                                <strong>Sponsorship Type:</strong> {{ ucfirst($student->sponsorship_type) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when academic year changes
    $('#academicYearSelect').on('change', function() {
        $('#yearFilterForm').submit();
    });
    
    // Function to activate tab based on URL hash
    function activateTabFromHash() {
        var hash = window.location.hash;
        if (hash && hash !== '#') {
            var tabId = hash.substring(1);
            if ($('#' + tabId).length) {
                $('.nav-pills .nav-link').removeClass('active');
                $('.tab-pane').removeClass('show active');
                
                $('a[href="#' + tabId + '"]').addClass('active');
                $('#' + tabId).addClass('show active');
                
                $('.nav-pills .nav-link').attr('aria-selected', 'false');
                $('a[href="#' + tabId + '"]').attr('aria-selected', 'true');
            } else {
                $('.nav-pills .nav-link:first').addClass('active');
                $('.tab-pane:first').addClass('show active');
            }
        } else {
            $('.nav-pills .nav-link:first').addClass('active');
            $('.tab-pane:first').addClass('show active');
        }
    }
    
    activateTabFromHash();
    
    $('.nav-pills .nav-link').on('shown.bs.tab', function(e) {
        var href = $(this).attr('href');
        var tabId = href.substring(1);
        
        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        } else {
            window.location.hash = tabId;
        }
    });
    
    window.addEventListener('popstate', function() {
        activateTabFromHash();
    });
});
</script>
@endpush

@push('styles')
<style>
.nav-pills .nav-link {
    color: #495057;
    transition: all 0.3s;
}
.nav-pills .nav-link.active {
    background-color: #007bff;
    color: white;
}
.nav-pills .nav-link i {
    margin-right: 8px;
}
.tab-pane {
    padding-top: 20px;
}
.info-box {
    min-height: 100px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 20px;
}
.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 100px;
    width: 100px;
    text-align: center;
    font-size: 45px;
    line-height: 100px;
    background: rgba(0,0,0,0.2);
}
.info-box-content {
    padding: 10px 10px 10px 0;
    margin-left: 100px;
}
.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}
.table tfoot td {
    font-weight: bold;
}
</style>
@endpush
@endsection