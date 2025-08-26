@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Liquidated Form</h1>
            <p class="text-muted">Form #{{ $liquidatedForm->form_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Details
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    <form action="{{ route('finance.liquidated-forms.update', $liquidatedForm) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Form Details -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Form Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="form_number">Form Number <span class="text-danger">*</span></label>
                                    <input type="text" name="form_number" id="form_number" class="form-control @error('form_number') is-invalid @enderror" 
                                           value="{{ old('form_number', $liquidatedForm->form_number) }}" required>
                                    @error('form_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                                           value="{{ old('title', $liquidatedForm->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects ?? [] as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id', $liquidatedForm->project_id) == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="liquidation_date">Liquidation Date <span class="text-danger">*</span></label>
                                    <input type="date" name="liquidation_date" id="liquidation_date" class="form-control @error('liquidation_date') is-invalid @enderror" 
                                           value="{{ old('liquidation_date', $liquidatedForm->liquidation_date->format('Y-m-d')) }}" required>
                                    @error('liquidation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_covered_start">Period Covered Start <span class="text-danger">*</span></label>
                                    <input type="date" name="period_covered_start" id="period_covered_start" class="form-control @error('period_covered_start') is-invalid @enderror" 
                                           value="{{ old('period_covered_start', $liquidatedForm->period_covered_start->format('Y-m-d')) }}" required>
                                    @error('period_covered_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_covered_end">Period Covered End <span class="text-danger">*</span></label>
                                    <input type="date" name="period_covered_end" id="period_covered_end" class="form-control @error('period_covered_end') is-invalid @enderror" 
                                           value="{{ old('period_covered_end', $liquidatedForm->period_covered_end->format('Y-m-d')) }}" required>
                                    @error('period_covered_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $liquidatedForm->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $liquidatedForm->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Form Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Form
                            </button>
                            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Status -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Current Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <span class="badge badge-{{ $liquidatedForm->status_color }} badge-lg">
                                {{ ucfirst(str_replace('_', ' ', $liquidatedForm->status)) }}
                            </span>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <strong>Created:</strong> {{ $liquidatedForm->created_at->format('M d, Y g:i A') }}<br>
                            <strong>Last Updated:</strong> {{ $liquidatedForm->updated_at->format('M d, Y g:i A') }}
                        </div>
                    </div>
                </div>

                <!-- Validation Rules -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Validation Rules</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success"></i> Form number must be unique</li>
                            <li><i class="fas fa-check text-success"></i> Period end must be after start</li>
                            <li><i class="fas fa-check text-success"></i> Liquidation date must be within period</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Date validation
    $('#period_covered_end').change(function() {
        const startDate = $('#period_covered_start').val();
        const endDate = $(this).val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('Period end date must be after start date.');
            $(this).val('');
        }
    });

    $('#liquidation_date').change(function() {
        const startDate = $('#period_covered_start').val();
        const endDate = $('#period_covered_end').val();
        const liquidationDate = $(this).val();
        
        if (startDate && endDate && liquidationDate) {
            if (liquidationDate < startDate || liquidationDate > endDate) {
                alert('Liquidation date must be within the covered period.');
                $(this).val('');
            }
        }
    });
});
</script>
@endpush
@endsection
