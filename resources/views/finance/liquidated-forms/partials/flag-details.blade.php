@if($liquidatedForm->status === 'flagged')
<div class="card shadow mb-4 border-danger">
    <div class="card-header py-3 bg-danger text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-flag"></i> Flagged for Review
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold text-danger">Flag Information</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Flagged By:</strong></td>
                        <td>{{ $liquidatedForm->flaggedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->flaggedBy->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Flagged On:</strong></td>
                        <td>{{ $liquidatedForm->flagged_at ? $liquidatedForm->flagged_at->format('M d, Y g:i A') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Priority:</strong></td>
                        <td>
                            @php
                                $priorityColors = [
                                    'low' => 'info',
                                    'medium' => 'warning',
                                    'high' => 'danger',
                                    'critical' => 'danger'
                                ];
                                $priorityColor = $priorityColors[$liquidatedForm->flag_priority ?? 'medium'] ?? 'warning';
                            @endphp
                            <span class="badge badge-{{ $priorityColor }}">
                                {{ ucfirst($liquidatedForm->flag_priority ?? 'medium') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Days Since Flagged:</strong></td>
                        <td>
                            @php
                                $daysSinceFlagged = $liquidatedForm->flagged_at ? $liquidatedForm->flagged_at->diffInDays(now()) : 0;
                                $daysColor = $daysSinceFlagged > 7 ? 'danger' : ($daysSinceFlagged > 3 ? 'warning' : 'success');
                            @endphp
                            <span class="text-{{ $daysColor }} font-weight-bold">{{ $daysSinceFlagged }} days</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold text-danger">Risk Assessment</h6>
                <div class="mb-2">
                    <strong>Variance Analysis:</strong>
                    @if($liquidatedForm->variance_percentage > 20)
                        <span class="badge badge-danger">High Risk</span>
                    @elseif($liquidatedForm->variance_percentage > 10)
                        <span class="badge badge-warning">Medium Risk</span>
                    @else
                        <span class="badge badge-success">Low Risk</span>
                    @endif
                </div>
                <div class="mb-2">
                    <strong>Receipts Coverage:</strong>
                    @if($liquidatedForm->receipts_coverage < 50)
                        <span class="badge badge-danger">Critical</span>
                    @elseif($liquidatedForm->receipts_coverage < 80)
                        <span class="badge badge-warning">Warning</span>
                    @else
                        <span class="badge badge-success">Good</span>
                    @endif
                </div>
                <div class="mb-2">
                    <strong>Amount at Risk:</strong>
                    <span class="text-danger font-weight-bold">{{ $liquidatedForm->formatted_variance_amount }}</span>
                </div>
            </div>
        </div>

        @if($liquidatedForm->flag_reason)
        <div class="mt-3">
            <h6 class="font-weight-bold text-danger">Reason for Flagging</h6>
            <div class="alert alert-danger">
                {!! nl2br(e($liquidatedForm->flag_reason)) !!}
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-3">
            @if(auth()->user()->role === 'finance')
                <button type="button" class="btn btn-success btn-sm" id="unflagBtn">
                    <i class="fas fa-flag-checkered"></i> Remove Flag
                </button>
            @endif
            @if(auth()->user()->role === 'admin')
                <button type="button" class="btn btn-warning btn-sm" id="requestClarificationBtn">
                    <i class="fas fa-question-circle"></i> Request Clarification
                </button>
            @endif
            <a href="{{ route('finance.liquidated-forms.print', $liquidatedForm) }}" 
               class="btn btn-info btn-sm" target="_blank">
                <i class="fas fa-print"></i> Print for Review
            </a>
        </div>
    </div>
</div>
@endif

@if($liquidatedForm->clarification_requested_by)
<div class="card shadow mb-4 border-warning">
    <div class="card-header py-3 bg-warning text-dark">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-question-circle"></i> Clarification Requested
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="font-weight-bold text-warning">Request Information</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Requested By:</strong></td>
                        <td>{{ $liquidatedForm->clarificationRequestedBy->first_name ?? 'N/A' }} {{ $liquidatedForm->clarificationRequestedBy->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Requested On:</strong></td>
                        <td>{{ $liquidatedForm->clarification_requested_at ? $liquidatedForm->clarification_requested_at->format('M d, Y g:i A') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Days Since Request:</strong></td>
                        <td>
                            @php
                                $daysSinceRequest = $liquidatedForm->clarification_requested_at ? $liquidatedForm->clarification_requested_at->diffInDays(now()) : 0;
                                $daysColor = $daysSinceRequest > 5 ? 'danger' : ($daysSinceRequest > 2 ? 'warning' : 'success');
                            @endphp
                            <span class="text-{{ $daysColor }} font-weight-bold">{{ $daysSinceRequest }} days</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="font-weight-bold text-warning">Clarification Notes</h6>
                <div class="alert alert-warning">
                    {{ $liquidatedForm->clarification_notes ?: 'No specific notes provided' }}
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> You have been notified of this clarification request. Please review the information and take appropriate action as needed.
            </div>
        </div>
    </div>
</div>
@endif
