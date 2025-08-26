@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Liquidated Form Details</h1>
            <p class="text-muted">Form #{{ $liquidatedForm->form_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($liquidatedForm->status === 'flagged')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-flag"></i>
        <strong>Flagged:</strong> This form has been flagged for review.
        @if($liquidatedForm->flag_reason)
            <br><strong>Reason:</strong> {{ $liquidatedForm->flag_reason }}
        @endif
        @if($liquidatedForm->flagged_at)
            <br><small>Flagged on {{ $liquidatedForm->flagged_at->format('M d, Y g:i A') }} by {{ $liquidatedForm->flaggedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->flaggedBy->last_name ?? '' }}</small>
        @endif
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

    @if($liquidatedForm->clarification_requested_by)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-question-circle"></i>
        <strong>Clarification Requested:</strong> Additional information is needed for this form.
        @if($liquidatedForm->clarification_notes)
            <br><strong>Notes:</strong> {{ $liquidatedForm->clarification_notes }}
        @endif
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
    @endif

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
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Form Number:</strong></td>
                                    <td>{{ $liquidatedForm->form_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $liquidatedForm->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Project:</strong></td>
                                    <td>{{ $liquidatedForm->project->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prepared By:</strong></td>
                                    <td>{{ $liquidatedForm->preparer->first_name ?? 'N/A' }} {{ $liquidatedForm->preparer->last_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Liquidation Date:</strong></td>
                                    <td>{{ $liquidatedForm->liquidation_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @switch($liquidatedForm->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('under_review')
                                                <span class="badge badge-info">Under Review</span>
                                                @break
                                            @case('flagged')
                                                <span class="badge badge-danger">Flagged</span>
                                                @break
                                            @case('clarification_requested')
                                                <span class="badge badge-warning">Clarification Requested</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ ucfirst($liquidatedForm->status) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Period Covered:</strong></td>
                                    <td>{{ $liquidatedForm->period_covered_start->format('M d, Y') }} - {{ $liquidatedForm->period_covered_end->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reviewed By:</strong></td>
                                    <td>{{ $liquidatedForm->reviewer->first_name ?? 'N/A' }} {{ $liquidatedForm->reviewer->last_name ?? '' }}</td>
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
                    </div>

                    @if($liquidatedForm->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Description:</strong></h6>
                            <p>{{ $liquidatedForm->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($liquidatedForm->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Notes:</strong></h6>
                            <p>{{ $liquidatedForm->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Financial Report Information -->
            @if($liquidatedForm->financialReport)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Linked Financial Report</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Report Title:</strong><br>
                        {{ $liquidatedForm->financialReport->title }}
                    </div>
                    <div class="mb-3">
                        <strong>Period:</strong><br>
                        {{ $liquidatedForm->financialReport->formatted_period }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $liquidatedForm->financialReport->status_badge_color }}">
                            {{ $liquidatedForm->financialReport->formatted_status }}
                        </span>
                    </div>
                    <a href="{{ route('pm.financial-reports.show', $liquidatedForm->financialReport) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> View Full Report
                    </a>
                </div>
            </div>
            @endif

            <!-- Linked Receipts from Financial Report -->
            @if($liquidatedForm->financialReport && $liquidatedForm->financialReport->directReceipts->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipts from Financial Report</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($liquidatedForm->financialReport->directReceipts as $receipt)
                        <div class="col-md-6 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">{{ Str::limit($receipt->original_file_name, 25) }}</h6>
                                            <p class="card-text text-muted mb-1">
                                                {{ $receipt->vendor_name }} - {{ $receipt->formatted_amount }}
                                            </p>
                                            <small class="text-muted">
                                                {{ $receipt->formatted_receipt_date }}
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <a href="{{ route('pm.receipts.download', $receipt) }}"
                                               class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('pm.receipts.show', $receipt) }}"
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Expenditures -->
            @if($liquidatedForm->expenditures->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Expenditures</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($liquidatedForm->expenditures as $expenditure)
                                <tr>
                                    <td>{{ $expenditure->expense_date->format('M d, Y') }}</td>
                                    <td>{{ $expenditure->description }}</td>
                                    <td>{{ $expenditure->category }}</td>
                                    <td class="text-right">₱{{ number_format($expenditure->pivot->amount_allocated ?? $expenditure->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Receipts -->
            @if($liquidatedForm->receipts->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receipts</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($liquidatedForm->receipts as $receipt)
                                <tr>
                                    <td>{{ $receipt->receipt_number }}</td>
                                    <td>{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                    <td>{{ $receipt->description }}</td>
                                    <td class="text-right">₱{{ number_format($receipt->amount, 2) }}</td>
                                    <td>
                                        @if($receipt->file_path)
                                        <a href="{{ route('pm.receipts.download', $receipt) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Revision History -->
            @if($liquidatedForm->revisions->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revision History</h6>
                </div>
                <div class="card-body">
                    @foreach($liquidatedForm->revisions as $revision)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">{{ $revision->created_at->format('M d, Y g:i A') }}</small>
                            <span class="badge badge-info">{{ $revision->version }}</span>
                        </div>
                        <p class="mb-1"><strong>{{ $revision->title }}</strong></p>
                        <p class="text-muted small">{{ $revision->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Activity Log -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Form Created</h6>
                                <p class="timeline-text">{{ $liquidatedForm->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($liquidatedForm->reviewed_by)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Reviewed</h6>
                                <p class="timeline-text">By {{ $liquidatedForm->reviewer->first_name ?? 'N/A' }} {{ $liquidatedForm->reviewer->last_name ?? '' }}</p>
                            </div>
                        </div>
                        @endif

                        @if($liquidatedForm->flagged_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Flagged</h6>
                                <p class="timeline-text">{{ $liquidatedForm->flagged_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($liquidatedForm->printed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Printed</h6>
                                <p class="timeline-text">{{ $liquidatedForm->printed_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}
</style>
@endsection
