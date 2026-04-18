<!-- resources/views/applications/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Application')

@section('content')
<div class="container mt-5">
    <h2>Application: {{ $application->application_number }}</h2>

    <ul class="nav nav-tabs mb-3" id="applicationTabs">
        <li class="nav-item"><a href="#personal" class="nav-link active">Personal Info</a></li>
        <li class="nav-item"><a href="#contact" class="nav-link">Contact Info</a></li>
        <li class="nav-item"><a href="#nextOfKin" class="nav-link">Next of Kin</a></li>
        <li class="nav-item"><a href="#academic" class="nav-link">Academic Info</a></li>
        <li class="nav-item"><a href="#programs" class="nav-link">Program Choices</a></li>
        <li class="nav-item"><a href="#documents" class="nav-link">Documents</a></li>
        <li class="nav-item"><a href="#submit" class="nav-link">Declaration & Submit</a></li>
    </ul>

    <div class="tab-content">
        <!-- Step 1: Personal Info -->
        <div class="tab-pane fade show active" id="personal">
            <form id="personalForm">
                @csrf
                <input type="text" name="first_name" value="{{ $application->personal_info['first_name'] ?? '' }}" class="form-control mb-2" placeholder="First Name">
                <input type="text" name="last_name" value="{{ $application->personal_info['last_name'] ?? '' }}" class="form-control mb-2" placeholder="Last Name">
                <button type="button" class="btn btn-primary" onclick="submitStep('personal')">Save & Next</button>
            </form>
        </div>

        <!-- Step 2: Contact Info -->
        <div class="tab-pane fade" id="contact">
            <form id="contactForm">
                @csrf
                <input type="text" name="phone" value="{{ $application->contact_info['phone'] ?? '' }}" class="form-control mb-2" placeholder="Phone">
                <button type="button" class="btn btn-primary" onclick="submitStep('contact')">Save & Next</button>
            </form>
        </div>

        <!-- Step 3: Next of Kin -->
        <div class="tab-pane fade" id="nextOfKin">
            <form id="nextOfKinForm">
                @csrf
                <input type="text" name="guardian_name" value="{{ $application->next_of_kin['guardian_name'] ?? '' }}" class="form-control mb-2" placeholder="Guardian Name">
                <button type="button" class="btn btn-primary" onclick="submitStep('nextOfKin')">Save & Next</button>
            </form>
        </div>

        <!-- Step 4,5,6,7 ... similarly -->
    </div>
</div>

<script>
function submitStep(step) {
    let formId = '';
    let route = '';
    switch(step){
        case 'personal':
            formId = 'personalForm';
            route = "{{ route('applicant.applications.personal', $application->id) }}";
            break;
        case 'contact':
            formId = 'contactForm';
            route = "{{ route('applicant.applications.contact', $application->id) }}";
            break;
        case 'nextOfKin':
            formId = 'nextOfKinForm';
            route = "{{ route('applicant.applications.nextofkin', $application->id) }}";
            break;
    }

    let formData = new FormData(document.getElementById(formId));

    fetch(route, {
        method: 'POST',
        body: formData,
        headers: {'X-CSRF-TOKEN': formData.get('_token')},
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert(data.message);
            // optionally move to next tab
        } else {
            alert('Error');
        }
    })
    .catch(err => console.log(err));
}
</script>
