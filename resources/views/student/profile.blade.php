@extends('layouts.students')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">
            
            <!-- Header Section -->
            <div class="text-center mb-4">
                <h2 class="mb-2">Student Profile</h2>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <span><strong>Current Year:</strong> {{ $currentAcademicYear ?? 'N/A' }}</span>
                    <span><strong>Last Login:</strong> {{ $lastLogin ?? 'N/A' }}</span>
                    <span><strong>Today:</strong> {{ now()->format('d M, Y') }}</span>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white text-center py-3">
                    <h5 class="mb-0">Profile</h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Profile Picture - Centered with Change Button -->
                    <div class="text-center mb-4">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                 class="rounded-circle"
                                 style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #ddd;">
                        @else
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                 style="width: 100px; height: 100px;">
                                <i class="feather-user fa-3x text-white"></i>
                            </div>
                        @endif
                        
                    </div>

                    <!-- Centered Table WITH BORDER -->
                    <div class="table-responsive">
                        <table class="table table-bordered" style="margin: 0 auto; width: 100%; border: 1px solid #dee2e6;">
                            <tbody>
                                <!-- Row 1: Name & Reg No -->
                                <tr>
                                    <td style="width: 25%; text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Name :</strong></td>
                                    <td style="width: 25%; text-align: left; padding: 10px;">{{ $user->last_name }}, {{ $user->middle_name }} {{ $user->first_name }}</td>
                                    <td style="width: 25%; text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Reg No. :</strong></td>
                                    <td style="width: 25%; text-align: left; padding: 10px;">{{ $student->registration_number }}</td>
                                </tr>
                                
                                <!-- Row 2: Gender & Admission No -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Gender :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ ucfirst($user->gender ?? 'N/A') }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Admission No. :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->registration_number }}</td>
                                </tr>
                                
                                <!-- Row 3: Date of Birth & Mode of Entry -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Date of Birth :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $user->date_of_birth ? date('d-m-Y', strtotime($user->date_of_birth)) : 'N/A' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Mode of Entry :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $application->entry_level ?? $application->entry_mode ?? 'Direct' }}</td>
                                </tr>
                                
                                <!-- Row 4: Nationality & Study Level -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Nationality :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $application->nationality ?? 'Tanzanian' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Study Level :</strong></td>
                                    <td style="text-align: left; padding: 10px;">
                                        @php
                                            $level = $student->current_level ?? 1;
                                            if ($level <= 4) echo 'Certificate';
                                            elseif ($level <= 6) echo 'Diploma';
                                            else echo 'Bachelor';
                                        @endphp
                                    </td>
                                </tr>
                                
                                <!-- Row 5: Phone & Current Academic Year -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Phone :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $user->phone ?? 'N/A' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Current Academic Year :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->academicYear->name ?? 'N/A' }}</td>
                                </tr>
                                
                                <!-- Row 6: Email & Course Duration -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Email :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $user->email ?? 'N/A' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Course Duration :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->programme->duration ?? '3' }} Years</td>
                                </tr>
                                
                                <!-- Row 7: Address & Year of Study -->
                                <!-- Row 7: Address & Year of Study -->
<tr>
    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Address :</strong></td>
    <td style="text-align: left; padding: 10px;">{{ $user->current_address ?? $user->permanent_address ?? 'N/A' }}</td>
    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Year of Study :</strong></td>
    <td style="text-align: left; padding: 10px;">
        @php
            $ntaLevel = $student->current_level ?? 1;
            
            // Map NTA Level to Year of Study
            if ($ntaLevel <= 4) {
                $yearOfStudy = '1<sup>st</sup> Year';
            } elseif ($ntaLevel == 5) {
                $yearOfStudy = '2<sup>nd</sup> Year';
            } else {
                $yearOfStudy = '3<sup>rd</sup> Year';
            }
        @endphp
        {!! $yearOfStudy !!}
        <small class="text-muted">(NTA Level {{ $ntaLevel }})</small>
    </td>
</tr>
                                
                                <!-- Row 8: Kin & Class Stream -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Kin :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->guardian_name ?? 'N/A' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Class Stream :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->stream ?? 'N/A' }}</td>
                                </tr>
                                
                                <!-- Row 9: Sponsorship & Student Status -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Sponsorship :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $application->sponsorship_type ?? $student->sponsorship_type ?? 'Private Institution/Company' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Student Status :</strong></td>
                                    <td style="text-align: left; padding: 10px;">
                                        @if($student->status === 'active')
                                            Continue
                                        @else
                                            {{ ucfirst($student->status) }}
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Row 10: Bank Name & Account Number -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Bank Name :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->bank_name ?? 'N/A' }}</td>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Account Number :</strong></td>
                                    <td style="text-align: left; padding: 10px;">{{ $student->account_number ?? 'N/A' }}</td>
                                </tr>
                                
                                <!-- Row 11: Registered Date -->
                                <tr>
                                    <td style="text-align: right; padding: 10px; background-color: #f8f9fa;"><strong>Registered Date :</strong></td>
                                    <td style="text-align: left; padding: 10px;" colspan="3">{{ $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons - Centered -->
                    <div class="text-center mt-4">
                        <a href="{{ route('student.profile.edit') }}" class="btn btn-primary px-4">
                            <i class="feather-edit"></i> Edit My Info
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Upload Photo -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('student.profile.upload-photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="feather-camera me-2"></i> Upload Profile Picture
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div id="imagePreview">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                     class="img-fluid rounded-circle mb-2"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width: 100px; height: 100px;">
                                    <i class="feather-user fa-2x text-white"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    <input type="file" name="profile_photo" class="form-control" accept="image/*" required
                           onchange="previewImage(this)">
                    <small class="text-muted mt-2 d-block">
                        <i class="feather-info"></i> Accepted formats: JPG, PNG, GIF. Max size: 2MB
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Centered Profile Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 15px;
}

.card {
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.card-body {
    padding: 2rem;
}

/* Table with Border */
.table-bordered {
    border: 1px solid #dee2e6 !important;
}

.table-bordered td {
    border: 1px solid #dee2e6 !important;
}

.table td {
    vertical-align: middle;
}

.table strong {
    color: #5a5c69;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .table-bordered td {
        display: block;
        text-align: left !important;
        width: 100% !important;
    }
    
    .table-bordered td:first-child {
        background-color: #f8f9fa;
        font-weight: 600;
    }
}

/* Image Preview */
#imagePreview img {
    max-width: 100%;
    height: auto;
}
</style>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded-circle" style="width: 100px; height: 100px; object-fit: cover; border: 2px solid #ddd;">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection