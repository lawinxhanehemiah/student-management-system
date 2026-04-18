<div class="review-summary">
    <h3>Application Review Summary</h3>

    <h4>Applicant Info</h4>
    <p><strong>Name:</strong> {{ $personal->first_name ?? '' }} {{ $personal->middle_name ?? '' }} {{ $personal->last_name ?? '' }}</p>
    <p><strong>Gender:</strong> {{ $personal->gender ?? '-' }}</p>
    <p><strong>Date of Birth:</strong> {{ $personal->date_of_birth ?? '-' }}</p>
    <p><strong>Nationality:</strong> {{ $personal->nationality ?? '-' }}</p>

    <h4>Contact Info</h4>
    <p><strong>Phone:</strong> {{ $contact->phone ?? '-' }}</p>
    <p><strong>Email:</strong> {{ $contact->email ?? '-' }}</p>
    <p><strong>Region:</strong> {{ $contact->region ?? '-' }}</p>
    <p><strong>District:</strong> {{ $contact->district ?? '-' }}</p>

    <h4>Next of Kin</h4>
    <p><strong>Name:</strong> {{ $kin->guardian_name ?? '-' }}</p>
    <p><strong>Phone:</strong> {{ $kin->guardian_phone ?? '-' }}</p>
    <p><strong>Relationship:</strong> {{ $kin->relationship ?? '-' }}</p>

    <h4>Academic Records</h4>
    @if($academic)
        <p><strong>CSEE Index:</strong> {{ $academic->csee_index_number }}</p>
        <p><strong>School:</strong> {{ $academic->csee_school }}</p>
        <p><strong>Year:</strong> {{ $academic->csee_year }}</p>

        <h5>Subjects</h5>
        <ul>
            @foreach($subjects as $subject)
                <li>{{ $subject->subject }} - Grade: {{ $subject->grade }}, Points: {{ $subject->points }}</li>
            @endforeach
        </ul>
    @else
        <p>No academic record found.</p>
    @endif

    <h4>Program Choices</h4>
    <ul>
        <li>First Choice: {{ $firstProgram->name ?? '-' }}</li>
        <li>Second Choice: {{ $secondProgram->name ?? '-' }}</li>
        <li>Third Choice: {{ $thirdProgram->name ?? '-' }}</li>
        <li>Selected Program: {{ $selectedProgram->name ?? '-' }}</li>
    </ul>
</div>
