@extends('layouts.admission')

@section('title', 'Admission Letters')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Sent Admission Letters</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.offers.letters') }}" class="btn btn-primary btn-sm">
                            <i class="feather-mail"></i> Send New Letter
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $statistics['total'] }}</h3>
                                    <p>Total Letters Sent</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $statistics['by_intake']['March'] }}</h3>
                                    <p>March Intake</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $statistics['by_intake']['September'] }}</h3>
                                    <p>September Intake</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, App No..." value="{{ $search }}">
                            </div>
                            <div class="col-md-3">
                                <label>Intake</label>
                                <select name="intake" class="form-control">
                                    <option value="March" {{ $intake == 'March' ? 'selected' : '' }}>March</option>
                                    <option value="September" {{ $intake == 'September' ? 'selected' : '' }}>September</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary form-control">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>App No.</th>
                                    <th>Full Name</th>
                                    <th>Programme</th>
                                    <th>Intake</th>
                                    <th>Email</th>
                                    <th>Sent Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($letters as $letter)
                                    <tr>
                                        <td>{{ $letter->application_number }}</td>
                                        <td>{{ $letter->first_name }} {{ $letter->middle_name }} {{ $letter->last_name }}</td>
                                        <td>{{ $letter->programme_name }}</td>
                                        <td>{{ $letter->intake }}</td>
                                        <td>{{ $letter->email }}<br><small>{{ $letter->phone }}</small></td>
                                        <td>{{ \Carbon\Carbon::parse($letter->admission_letter_sent_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($letter->admission_status == 'admitted')
                                                <span class="badge badge-success">Accepted</span>
                                            @elseif($letter->admission_status == 'not_admitted')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admission.offers.letter.preview', $letter->id) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="feather-eye"></i> Preview
                                            </a>
                                            <a href="{{ route('admission.offers.letter.generate', $letter->id) }}" class="btn btn-sm btn-secondary">
                                                <i class="feather-download"></i> PDF
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No admission letters found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $letters->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection