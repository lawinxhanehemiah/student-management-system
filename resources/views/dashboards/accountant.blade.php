@extends('layouts.accountant')

@section('title', 'Accountant Dashboard')

@section('sidebar')
    <li class="nav-item"><a class="nav-link" href="#">Manage Users</a></li>
    <li class="nav-item"><a class="nav-link" href="#">Manage Roles</a></li>
    <li class="nav-item"><a class="nav-link" href="#">System Settings</a></li>
@endsection

@section('content')
    <h1>Welcome, {{ Auth::user()->first_name }}</h1>
    <p>This is your Accountant dashboard.</p>
@endsection
