@extends('dashboards.layouts.app')

@section('title', 'HOD Dashboard')

@section('sidebar')
    <li class="nav-item"><a class="nav-link" href="#">My Department</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Students</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Reports</a></li>
@endsection

@section('content')
    <h1>Welcome, {{ Auth::user()->name }}</h1>
    <p>Department: {{ Auth::user()->department?->name ?? 'No department assigned' }}</p>

    <h3>Students</h3>
    <ul>
        @forelse ($students ?? [] as $student)
            <li>{{ $student->name }} ({{ $student->registration_number }})</li>
        @empty
            <li>No students assigned to your department yet.</li>
        @endforelse
    </ul>
@endsection
