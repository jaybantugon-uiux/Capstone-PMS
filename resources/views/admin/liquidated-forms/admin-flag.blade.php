@extends('layouts.app')

@section('title', 'Admin Flag - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Admin Flag</h1>
                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Flag Form #{{ $liquidatedForm->form_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Form Details</h6>
                            <p><strong>Title:</strong> {{ $liquidatedForm->title }}</p>
                            <p><strong>Project:</strong> {{ $liquidatedForm->project->name ?? 'N/A' }}</p>
                            <p><strong>Total Amount:</strong> ₱{{ number_format($liquidatedForm->total_amount, 2) }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-{{ $liquidatedForm->status_badge_color }}">{{ $liquidatedForm->formatted_status }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Current Information</h6>
                            <p><strong>Preparer:</strong> {{ $liquidatedForm->preparer->name ?? 'N/A' }}</p>
                            <p><strong>Liquidation Date:</strong> {{ $liquidatedForm->formatted_liquidation_date }}</p>
                            <p><strong>Period Covered:</strong> {{ $liquidatedForm->formatted_period }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.liquidated-forms.admin-flag', $liquidatedForm) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="flag_reason" class="form-label">Flag Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('flag_reason') is-invalid @enderror" 
                                      id="flag_reason" 
                                      name="flag_reason" 
                                      rows="4" 
                                      required>{{ old('flag_reason') }}</textarea>
                            @error('flag_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Please provide a detailed explanation for why this form needs to be flagged.</div>
                        </div>

                        <div class="mb-3">
                            <label for="flag_priority" class="form-label">Priority Level <span class="text-danger">*</span></label>
                            <select class="form-select @error('flag_priority') is-invalid @enderror" id="flag_priority" name="flag_priority" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('flag_priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('flag_priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('flag_priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="critical" {{ old('flag_priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                            @error('flag_priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> Flagging this form will change its status to "Flagged" and may require additional review before it can proceed.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-flag"></i> Flag Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
