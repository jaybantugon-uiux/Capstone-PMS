@extends('layouts.app')

@section('title', 'Approve Revision - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Approve Revision</h1>
                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Approve Revision for Form #{{ $liquidatedForm->form_number }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Form Details</h6>
                            <p><strong>Title:</strong> {{ $liquidatedForm->title }}</p>
                            <p><strong>Project:</strong> {{ $liquidatedForm->project->name ?? 'N/A' }}</p>
                            <p><strong>Current Total Amount:</strong> ₱{{ number_format($liquidatedForm->total_amount, 2) }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-{{ $liquidatedForm->status_badge_color }}">{{ $liquidatedForm->formatted_status }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Revision Information</h6>
                            @php
                                $latestRevision = $liquidatedForm->revisions()->where('status', 'pending')->latest()->first();
                            @endphp
                            @if($latestRevision)
                                <p><strong>Revision Requested By:</strong> {{ $latestRevision->createdBy->name ?? 'N/A' }}</p>
                                <p><strong>Revision Date:</strong> {{ $latestRevision->created_at->format('M d, Y H:i') }}</p>
                                <p><strong>Revision Reason:</strong> {{ $latestRevision->reason }}</p>
                            @else
                                <p class="text-muted">No pending revision found.</p>
                            @endif
                        </div>
                    </div>

                    @if($latestRevision)
                        <form method="POST" action="{{ route('admin.liquidated-forms.approve-revision', $liquidatedForm) }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="approved_amount" class="form-label">Approved Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" 
                                           class="form-control @error('approved_amount') is-invalid @enderror" 
                                           id="approved_amount" 
                                           name="approved_amount" 
                                           value="{{ old('approved_amount', $liquidatedForm->total_amount) }}"
                                           step="0.01" 
                                           min="0">
                                </div>
                                @error('approved_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to keep the current amount.</div>
                            </div>

                            <div class="mb-3">
                                <label for="approval_notes" class="form-label">Approval Notes</label>
                                <textarea class="form-control @error('approval_notes') is-invalid @enderror" 
                                          id="approval_notes" 
                                          name="approval_notes" 
                                          rows="4">{{ old('approval_notes') }}</textarea>
                                @error('approval_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Optional notes about the approval decision.</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve Revision
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No pending revision found for this form.
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                Back to Form
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
