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
            <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            @if($liquidatedForm->status === 'pending')
            <a href="{{ route('finance.liquidated-forms.edit', $liquidatedForm) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a href="{{ route('finance.liquidated-forms.print', $liquidatedForm) }}" class="btn btn-info btn-sm" target="_blank">
                <i class="fas fa-print"></i> Print
            </a>
                         @if($liquidatedForm->status === 'flagged')
             <a href="{{ route('finance.liquidated-forms.unflag.form', $liquidatedForm) }}" class="btn btn-success btn-sm">
                 <i class="fas fa-flag-checkered"></i> Unflag
             </a>
             @else
             <a href="{{ route('finance.liquidated-forms.flag.form', $liquidatedForm) }}" class="btn btn-danger btn-sm">
                 <i class="fas fa-flag"></i> Flag
             </a>
             @endif
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
                                        <span class="badge badge-{{ $liquidatedForm->status_badge_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $liquidatedForm->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Period Covered:</strong></td>
                                    <td>{{ $liquidatedForm->period_covered_start->format('M d, Y') }} - {{ $liquidatedForm->period_covered_end->format('M d, Y') }}</td>
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
                    <a href="{{ route('finance.financial-reports.show', $liquidatedForm->financialReport) }}" 
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
                                            <a href="{{ route('finance.receipts.download', $receipt) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="{{ route('finance.receipts.show', $receipt) }}" 
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
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Approval Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Approval Information</h6>
                </div>
                <div class="card-body">
                    @if($liquidatedForm->reviewer)
                    <div class="mb-3">
                        <strong>Reviewed By:</strong><br>
                        {{ $liquidatedForm->reviewer->first_name }} {{ $liquidatedForm->reviewer->last_name }}<br>
                        <small class="text-muted">
                            @if($liquidatedForm->reviewed_at)
                                {{ $liquidatedForm->reviewed_at->format('M d, Y g:i A') }}
                            @endif
                        </small>
                    </div>
                    @endif


                </div>
            </div>

            <!-- Print Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Print Information</h6>
                </div>
                <div class="card-body">
                    @if($liquidatedForm->printed_at)
                    <div class="mb-3">
                        <strong>Printed By:</strong><br>
                        {{ $liquidatedForm->printedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->printedBy->last_name ?? '' }}<br>
                        <small class="text-muted">{{ $liquidatedForm->printed_at->format('M d, Y g:i A') }}</small>
                    </div>
                    @else
                    <p class="text-muted mb-0">Not yet printed</p>
                    @endif
                </div>
            </div>

            <!-- Revision History -->
            @if($liquidatedForm->revisions->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revision History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($liquidatedForm->revisions as $revision)
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $revision->revision_type }}</h6>
                                <p class="timeline-text">{{ $revision->reason }}</p>
                                <small class="text-muted">
                                    {{ $revision->created_at->format('M d, Y g:i A') }} by 
                                    {{ $revision->requester->first_name ?? 'N/A' }} {{ $revision->requester->last_name ?? '' }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Clarification Request -->
            @if($liquidatedForm->clarification_requested_by)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-question-circle"></i> Clarification Requested
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Requested By:</strong><br>
                        {{ $liquidatedForm->clarificationRequestedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->clarificationRequestedBy->last_name ?? '' }}<br>
                        <small class="text-muted">
                            {{ $liquidatedForm->clarification_requested_at ? $liquidatedForm->clarification_requested_at->format('M d, Y g:i A') : 'N/A' }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <strong>Clarification Notes:</strong><br>
                        <p class="text-muted">{{ $liquidatedForm->clarification_notes ?: 'No notes provided' }}</p>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> You have been notified of this clarification request. Please review the information and take appropriate action as needed.
                    </div>
                </div>
            </div>
            @endif

            <!-- Form Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.liquidated-forms.print', $liquidatedForm) }}" 
                           class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-print"></i> Print Form
                        </a>
                        @if($liquidatedForm->status === 'pending')
                        <a href="{{ route('finance.liquidated-forms.edit', $liquidatedForm) }}" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Form
                        </a>
                        @endif
                                                 @if($liquidatedForm->status === 'flagged')
                         <a href="{{ route('finance.liquidated-forms.unflag.form', $liquidatedForm) }}" class="btn btn-success btn-sm">
                             <i class="fas fa-flag-checkered"></i> Remove Flag
                         </a>
                         @else
                         <a href="{{ route('finance.liquidated-forms.flag.form', $liquidatedForm) }}" class="btn btn-danger btn-sm">
                             <i class="fas fa-flag"></i> Flag for Review
                         </a>
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
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #007bff;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: bold;
}

.timeline-text {
    margin-bottom: 5px;
    color: #666;
}
</style>
@endsection
