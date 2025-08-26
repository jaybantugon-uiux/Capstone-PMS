@extends('layouts.app')

@section('title', 'Revision History - Liquidated Form')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 mb-0">Revision History</h1>
                <a href="{{ route('admin.liquidated-forms.show', $liquidatedForm) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revision History for Form #{{ $liquidatedForm->form_number }}</h5>
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
                            <h6>Revision Summary</h6>
                            <p><strong>Total Revisions:</strong> {{ $revisions->count() }}</p>
                            <p><strong>Pending Revisions:</strong> {{ $revisions->where('status', 'pending')->count() }}</p>
                            <p><strong>Approved Revisions:</strong> {{ $revisions->where('status', 'approved')->count() }}</p>
                        </div>
                    </div>

                    @if($revisions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Revision #</th>
                                        <th>Requested By</th>
                                        <th>Requested Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revisions as $revision)
                                        <tr>
                                            <td>{{ $revision->id }}</td>
                                            <td>{{ $revision->createdBy->name ?? 'N/A' }}</td>
                                            <td>{{ $revision->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="{{ $revision->reason }}">
                                                    {{ $revision->reason }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($revision->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($revision->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($revision->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $revision->approvedBy->name ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.liquidated-forms.show-revision', [$liquidatedForm, $revision]) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No revision history found for this form.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
