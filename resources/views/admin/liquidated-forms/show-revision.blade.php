@extends('layouts.app')

@section('title', 'View Revision - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">View Revision</h1>
                <div>
                    <a href="{{ route('admin.liquidated-forms.revisions', $liquidatedForm) }}" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Revision History
                    </a>
                    <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Form
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revision #{{ $revision->id }} for Form #{{ $liquidatedForm->form_number }}</h5>
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
                            <h6>Revision Information</h6>
                            <p><strong>Revision ID:</strong> #{{ $revision->id }}</p>
                            <p><strong>Status:</strong> 
                                @if($revision->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($revision->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($revision->status) }}</span>
                                @endif
                            </p>
                            <p><strong>Requested By:</strong> {{ $revision->createdBy->name ?? 'N/A' }}</p>
                            <p><strong>Requested Date:</strong> {{ $revision->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Revision Details</h6>
                                </div>
                                <div class="card-body">
                                    <h6>Reason for Revision</h6>
                                    <div class="border rounded p-3 bg-light mb-3">
                                        {{ $revision->reason }}
                                    </div>

                                    @if($revision->notes)
                                        <h6>Additional Notes</h6>
                                        <div class="border rounded p-3 bg-light mb-3">
                                            {{ $revision->notes }}
                                        </div>
                                    @endif

                                    @if($revision->status === 'approved')
                                        <h6>Approval Information</h6>
                                        <p><strong>Approved By:</strong> {{ $revision->approvedBy->name ?? 'N/A' }}</p>
                                        <p><strong>Approved Date:</strong> {{ $revision->approved_at ? $revision->approved_at->format('M d, Y H:i') : 'N/A' }}</p>
                                        @if($revision->approved_amount)
                                            <p><strong>Approved Amount:</strong> ₱{{ number_format($revision->approved_amount, 2) }}</p>
                                        @endif
                                        @if($revision->approval_notes)
                                            <p><strong>Approval Notes:</strong></p>
                                            <div class="border rounded p-3 bg-light">
                                                {{ $revision->approval_notes }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    @if($revision->status === 'pending')
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('admin.liquidated-forms.approve-revision.form', $liquidatedForm) }}" 
                                               class="btn btn-success">
                                                <i class="fas fa-check"></i> Approve Revision
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            This revision has already been processed.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
