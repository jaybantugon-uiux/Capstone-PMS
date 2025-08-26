@extends('layouts.app')

@section('title', 'Admin Unflag - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Admin Unflag</h1>
                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Unflag Form #{{ $liquidatedForm->form_number }}</h5>
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
                            <h6>Flag Information</h6>
                            <p><strong>Flagged By:</strong> {{ $liquidatedForm->flaggedBy->name ?? 'N/A' }}</p>
                            <p><strong>Flagged At:</strong> {{ $liquidatedForm->flagged_at ? $liquidatedForm->flagged_at->format('M d, Y H:i') : 'N/A' }}</p>
                            <p><strong>Flag Reason:</strong></p>
                            <div class="border rounded p-2 bg-light">
                                {{ $liquidatedForm->flag_reason ?? 'No reason provided' }}
                            </div>
                            <p><strong>Priority:</strong> <span class="badge bg-{{ $liquidatedForm->flag_priority_color }}">{{ ucfirst($liquidatedForm->flag_priority) }}</span></p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.liquidated-forms.admin-unflag', $liquidatedForm) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="unflag_notes" class="form-label">Unflag Notes</label>
                            <textarea class="form-control @error('unflag_notes') is-invalid @enderror" 
                                      id="unflag_notes" 
                                      name="unflag_notes" 
                                      rows="4">{{ old('unflag_notes') }}</textarea>
                            @error('unflag_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional notes about why this form is being unflagged.</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Unflagging this form will change its status back to "Pending" and allow it to proceed with normal processing.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Unflag Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
