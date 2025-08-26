@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Flag Liquidated Form</h1>
            <p class="text-muted">Form #{{ $liquidatedForm->form_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Form
            </a>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Warning:</strong> Flagging a form will mark it for review and notify administrators.
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Flag Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-flag"></i> Flag Form #{{ $liquidatedForm->form_number }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('finance.liquidated-forms.flag', $liquidatedForm) }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="flag_reason" class="font-weight-bold">
                                Reason for Flagging <span class="text-danger">*</span>
                            </label>
                            <textarea name="flag_reason" id="flag_reason" class="form-control @error('flag_reason') is-invalid @enderror" 
                                      rows="4" required 
                                      placeholder="Please provide a detailed reason for flagging this form. Be specific about the issues or concerns...">{{ old('flag_reason') }}</textarea>
                            <small class="form-text text-muted">This reason will be visible to administrators and may be used for audit purposes.</small>
                            @error('flag_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="flag_priority" class="font-weight-bold">Priority Level</label>
                            <select name="flag_priority" id="flag_priority" class="form-control @error('flag_priority') is-invalid @enderror">
                                <option value="low" {{ old('flag_priority') == 'low' ? 'selected' : '' }}>
                                    Low - Minor issues that need attention
                                </option>
                                <option value="medium" {{ old('flag_priority', 'medium') == 'medium' ? 'selected' : '' }}>
                                    Medium - Standard review required
                                </option>
                                <option value="high" {{ old('flag_priority') == 'high' ? 'selected' : '' }}>
                                    High - Urgent attention needed
                                </option>
                                <option value="critical" {{ old('flag_priority') == 'critical' ? 'selected' : '' }}>
                                    Critical - Immediate action required
                                </option>
                            </select>
                            <small class="form-text text-muted">Select the appropriate priority level based on the severity of the issue.</small>
                            @error('flag_priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="flag_notes" class="font-weight-bold">Additional Notes (Optional)</label>
                            <textarea name="flag_notes" id="flag_notes" class="form-control @error('flag_notes') is-invalid @enderror" 
                                      rows="2" 
                                      placeholder="Any additional notes or recommendations...">{{ old('flag_notes') }}</textarea>
                            @error('flag_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-flag"></i> Flag Form
                            </button>
                            <a href="{{ route('finance.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Form Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Form Number:</strong><br>
                        {{ $liquidatedForm->form_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Title:</strong><br>
                        {{ $liquidatedForm->title }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $liquidatedForm->status_badge_color }}">
                            {{ ucfirst(str_replace('_', ' ', $liquidatedForm->status)) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Prepared By:</strong><br>
                        {{ $liquidatedForm->preparer->first_name ?? 'N/A' }} {{ $liquidatedForm->preparer->last_name ?? '' }}
                    </div>
                    <div class="mb-3">
                        <strong>Liquidation Date:</strong><br>
                        {{ $liquidatedForm->liquidation_date->format('M d, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
