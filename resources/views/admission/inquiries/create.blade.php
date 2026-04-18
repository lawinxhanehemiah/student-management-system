@extends('layouts.admission')

@section('title', 'New Inquiry')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create New Inquiry</h4>
                    <div class="card-tools">
                        <a href="{{ route('admission.inquiries.index') }}" class="btn btn-secondary btn-sm">
                            <i class="feather-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admission.inquiries.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                                    @error('full_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                    @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                    @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Inquiry Type <span class="text-danger">*</span></label>
                                    <select name="inquiry_type" class="form-control @error('inquiry_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="general" {{ old('inquiry_type') == 'general' ? 'selected' : '' }}>General</option>
                                        <option value="admission" {{ old('inquiry_type') == 'admission' ? 'selected' : '' }}>Admission</option>
                                        <option value="program" {{ old('inquiry_type') == 'program' ? 'selected' : '' }}>Program</option>
                                        <option value="payment" {{ old('inquiry_type') == 'payment' ? 'selected' : '' }}>Payment</option>
                                        <option value="technical" {{ old('inquiry_type') == 'technical' ? 'selected' : '' }}>Technical</option>
                                        <option value="complaint" {{ old('inquiry_type') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                                        <option value="other" {{ old('inquiry_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('inquiry_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Subject <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                                    @error('subject') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Priority <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="5" required>{{ old('message') }}</textarea>
                            @error('message') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Assign To</label>
                                    <select name="assigned_to" class="form-control">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($staff as $staffMember)
                                            <option value="{{ $staffMember->id }}" {{ old('assigned_to') == $staffMember->id ? 'selected' : '' }}>
                                                {{ $staffMember->first_name }} {{ $staffMember->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Internal Notes</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-save"></i> Create Inquiry
                        </button>
                        <a href="{{ route('admission.inquiries.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection