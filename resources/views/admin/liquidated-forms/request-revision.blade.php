@extends('app')

@section('title', 'Request Revision - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Request Revision</h1>
                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Request Revision for Form #{{ $liquidatedForm->form_number }}</h5>
                </div>
                <div class="card-body">
                    @if($liquidatedForm->status === 'flagged')
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> This form is currently flagged. Requesting a revision will change the status to "Revision Requested" and override the flagged status. This is appropriate when the admin determines that a revision is needed instead of or in addition to the flag.
                        </div>
                    @endif

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

                    <form method="POST" action="{{ route('admin.liquidated-forms.request-revision', $liquidatedForm) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Revision Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" 
                                      name="reason" 
                                      rows="4" 
                                      required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Please provide a detailed explanation for why this revision is needed.</div>
                        </div>

                        <div class="mb-3">
                            <label for="revision_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('revision_notes') is-invalid @enderror" 
                                      id="revision_notes" 
                                      name="revision_notes" 
                                      rows="3">{{ old('revision_notes') }}</textarea>
                            @error('revision_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Request Revision
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
