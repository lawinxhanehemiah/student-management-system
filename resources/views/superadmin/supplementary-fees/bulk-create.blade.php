{{-- resources/views/superadmin/supplementary-fees/bulk-create.blade.php --}}
@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i>
                        Bulk Add Supplementary Fees - {{ $programme->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('superadmin.programmes.supplementary-fees.index', $programme->id) }}" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Supplementary Fee (EXAMS ONLY):</strong> 
                        Mwanafunzi anafanya MTIHANI TU wa somo aliloshindwa.
                        <br>Hii ni ada ya **JUMLA** kwa muhula, si kwa kila somo.
                    </div>

                    <form method="POST" 
                          action="{{ route('superadmin.programmes.supplementary-fees.bulk-store', $programme->id) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Fee (TZS) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">TZS</span>
                                        </div>
                                        <input type="number" 
                                               name="total_fee" 
                                               class="form-control" 
                                               value="200000" 
                                               step="1000" 
                                               min="0" 
                                               required>
                                    </div>
                                    <small class="text-muted">
                                        Jumla ya ada ya supplementary kwa muhula (sio kwa kila somo)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label>Select Levels and Semesters <span class="text-danger">*</span></label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-warning">
                                            <tr>
                                                <th>Level/Semester</th>
                                                <th>Semester 1</th>
                                                <th>Semester 2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($levels as $level)
                                            <tr>
                                                <td><strong>Year {{ $level }}</strong></td>
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               name="selections[{{ $level }}][1]" 
                                                               class="form-check-input"
                                                               value="1">
                                                        <label class="form-check-label">Select</label>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               name="selections[{{ $level }}][2]" 
                                                               class="form-check-input"
                                                               value="1">
                                                        <label class="form-check-label">Select</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Unaweza kuchagua level nyingi kwa wakati mmoja
                                </small>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" checked>
                                    <label class="form-check-label">
                                        Active (Fee itakayotumika kwa mwaka huu)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Save Selected Supplementary Fees
                            </button>
                            <a href="{{ route('superadmin.programmes.supplementary-fees.index', $programme->id) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection