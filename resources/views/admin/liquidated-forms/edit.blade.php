@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Liquidated Form</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> View Form
            </a>
            <a href="{{ route('admin.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Current Form Information -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Current Form Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Form Number:</strong></td>
                            <td>{{ $liquidatedForm->form_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-{{ $liquidatedForm->status_badge_color }}">
                                    {{ $liquidatedForm->formatted_status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $liquidatedForm->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $liquidatedForm->updated_at->format('M d, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Total Amount:</strong></td>
                            <td><strong>₱{{ number_format($liquidatedForm->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Total Receipts:</strong></td>
                            <td>₱{{ number_format($liquidatedForm->total_receipts, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Variance:</strong></td>
                            <td>₱{{ number_format($liquidatedForm->variance_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Project:</strong></td>
                            <td>
                                @if($liquidatedForm->project)
                                    <a href="{{ route('projects.show', $liquidatedForm->project) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-project-diagram"></i> {{ $liquidatedForm->project->name }}
                                    </a>
                                @else
                                    <span class="text-muted">No Project</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Form Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.liquidated-forms.update', $liquidatedForm) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Form Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $liquidatedForm->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="project_id">Project</label>
                            <select class="form-control @error('project_id') is-invalid @enderror" 
                                    id="project_id" name="project_id">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
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
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prepared_by">Preparer <span class="text-danger">*</span></label>
                            <select class="form-control @error('prepared_by') is-invalid @enderror" 
                                    id="prepared_by" name="prepared_by" required>
                                <option value="">Select Preparer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('prepared_by', $liquidatedForm->prepared_by) == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prepared_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $liquidatedForm->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="liquidation_date">Liquidation Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('liquidation_date') is-invalid @enderror" 
                                   id="liquidation_date" name="liquidation_date" 
                                   value="{{ old('liquidation_date', $liquidatedForm->liquidation_date ? $liquidatedForm->liquidation_date->format('Y-m-d') : '') }}" required>
                            @error('liquidation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period_covered_start">Period Start <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('period_covered_start') is-invalid @enderror" 
                                   id="period_covered_start" name="period_covered_start" 
                                   value="{{ old('period_covered_start', $liquidatedForm->period_covered_start ? $liquidatedForm->period_covered_start->format('Y-m-d') : '') }}" required>
                            @error('period_covered_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period_covered_end">Period End <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('period_covered_end') is-invalid @enderror" 
                                   id="period_covered_end" name="period_covered_end" 
                                   value="{{ old('period_covered_end', $liquidatedForm->period_covered_end ? $liquidatedForm->period_covered_end->format('Y-m-d') : '') }}" required>
                            @error('period_covered_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control @error('total_amount') is-invalid @enderror" 
                                   id="total_amount" name="total_amount" 
                                   value="{{ old('total_amount', $liquidatedForm->total_amount) }}" required>
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="total_receipts">Total Receipts</label>
                            <input type="number" step="0.01" min="0" class="form-control @error('total_receipts') is-invalid @enderror" 
                                   id="total_receipts" name="total_receipts" 
                                   value="{{ old('total_receipts', $liquidatedForm->total_receipts) }}">
                            @error('total_receipts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $liquidatedForm->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes', $liquidatedForm->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Liquidated Form
                    </button>
                    <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Client-side validation for date ranges
document.getElementById('period_covered_end').addEventListener('change', function() {
    const startDate = document.getElementById('period_covered_start').value;
    const endDate = this.value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('Period end date must be after or equal to period start date');
        this.value = '';
    }
});

document.getElementById('period_covered_start').addEventListener('change', function() {
    const startDate = this.value;
    const endDate = document.getElementById('period_covered_end').value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('Period start date must be before or equal to period end date');
        document.getElementById('period_covered_end').value = '';
    }
});
</script>
@endpush
