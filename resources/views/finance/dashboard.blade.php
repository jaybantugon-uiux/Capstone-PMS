@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Finance Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-invoice-dollar"></i> Liquidated Forms
            </a>
            <a href="{{ route('finance.expenditures.index') }}" class="btn btn-success btn-sm">
                <i class="fas fa-receipt"></i> Daily Expenditures
            </a>
            <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-chart-line"></i> Financial Reports
            </a>
            <a href="{{ route('finance.receipts.index') }}" class="btn btn-info btn-sm">
                <i class="fas fa-file-upload"></i> Receipts
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Liquidated Forms -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Liquidated Forms
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $liquidatedFormsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingReviewCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flagged Forms -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Flagged Forms
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $flaggedFormsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-flag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Amount Processed -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Amount Processed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($totalAmountProcessed ?? 0, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="row mb-4">
        <!-- Suspicious Activities -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Suspicious Activities
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suspiciousActivitiesCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Reports -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Financial Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $financialReportsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Reports -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Generated Reports
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $generatedReportsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Daily Expenditures -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Daily Expenditures
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dailyExpendituresCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Left Column - Tables -->
        <div class="col-lg-8">
            <!-- Recent Liquidated Forms -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Liquidated Forms</h6>
                    <a href="{{ route('finance.liquidated-forms.index') }}" class="btn btn-primary btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentLiquidatedForms) && $recentLiquidatedForms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Form #</th>
                                        <th>Project</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLiquidatedForms->take(5) as $form)
                                    <tr>
                                        <td>{{ $form->form_number }}</td>
                                        <td>{{ $form->project->name ?? 'N/A' }}</td>
                                        <td>₱{{ number_format($form->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $form->status_color }}">
                                                {{ ucfirst(str_replace('_', ' ', $form->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.liquidated-forms.show', $form) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($form->status === 'pending')
                                            <a href="{{ route('finance.liquidated-forms.edit', $form) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No liquidated forms found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Financial Reports -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">Recent Financial Reports</h6>
                    <a href="{{ route('finance.financial-reports.index') }}" class="btn btn-warning btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentFinancialReports) && $recentFinancialReports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentFinancialReports->take(5) as $report)
                                    <tr>
                                        <td>{{ Str::limit($report->title, 30) }}</td>
                                        <td>{{ ucfirst($report->report_type) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $report->status_color }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('finance.financial-reports.show', $report) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($report->status === 'draft')
                                            <a href="{{ route('finance.financial-reports.edit', $report) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No financial reports found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Receipts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">Recent Receipts</h6>
                    <a href="{{ route('finance.receipts.index') }}" class="btn btn-info btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($recentReceipts) && $recentReceipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Vendor</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReceipts->take(5) as $receipt)
                                    <tr>
                                        <td>{{ $receipt->receipt_number ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($receipt->vendor_name, 20) }}</td>
                                        <td>₱{{ number_format($receipt->amount, 2) }}</td>
                                        <td>{{ ucfirst($receipt->receipt_type) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $receipt->status === 'pending' ? 'warning' : ($receipt->status === 'approved' ? 'success' : 'secondary') }}">
                                                {{ ucfirst($receipt->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.receipts.show', $receipt) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($receipt->status === 'pending')
                                            <a href="{{ route('finance.receipts.edit', $receipt) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No receipts found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Actions & Widgets -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.financial-reports.create') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-plus"></i> Create Financial Report
                        </a>

                        <a href="{{ route('finance.liquidated-forms.suspicious-activities') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-exclamation-triangle"></i> Flag Suspicious Activities
                        </a>
                        
                        <a href="{{ route('finance.liquidated-forms.export.csv') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                        <a href="{{ route('finance.receipts.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-upload"></i> Upload Receipts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell"></i> Recent Notifications
                    </h6>
                    <a href="{{ route('finance.notifications.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $notifications = auth()->user()->notifications()
                            ->whereIn('type', [
                                'App\Notifications\ExpenseLiquidationNotification'
                            ])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @if($notifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            @if($notification->data['type'] === 'revision_requested')
                                                <i class="fas fa-redo text-warning"></i>
                                            @elseif($notification->data['type'] === 'clarification_requested')
                                                <i class="fas fa-question-circle text-info"></i>
                                            @else
                                                <i class="fas fa-bell text-primary"></i>
                                            @endif
                                            {{ $notification->data['type'] === 'revision_requested' ? 'Revision Requested' : ($notification->data['type'] === 'clarification_requested' ? 'Clarification Requested' : 'Notification') }}
                                        </h6>
                                        <p class="mb-1 text-sm">
                                            @if($notification->data['type'] === 'revision_requested')
                                                @if(isset($notification->data['form_number']))
                                                    Form #{{ $notification->data['form_number'] }} - {{ Str::limit($notification->data['revision_reason'] ?? 'No reason provided', 80) }}
                                                @else
                                                    {{ Str::limit($notification->data['revision_reason'] ?? 'Revision requested', 80) }}
                                                @endif
                                            @elseif($notification->data['type'] === 'clarification_requested')
                                                @if(isset($notification->data['form_number']))
                                                    Form #{{ $notification->data['form_number'] }} - {{ Str::limit($notification->data['clarification_question'] ?? 'No question provided', 80) }}
                                                @elseif(isset($notification->data['receipt_number']))
                                                    Receipt #{{ $notification->data['receipt_number'] }} ({{ $notification->data['vendor_name'] ?? 'N/A' }}) - {{ Str::limit($notification->data['clarification_question'] ?? 'No question provided', 80) }}
                                                @elseif(isset($notification->data['message']))
                                                    {{ Str::limit($notification->data['message'], 80) }}
                                                @else
                                                    {{ Str::limit($notification->data['clarification_question'] ?? 'Clarification requested', 80) }}
                                                @endif
                                            @else
                                                {{ Str::limit($notification->data['message'] ?? 'New notification', 80) }}
                                            @endif
                                        </p>
                                        <small class="text-muted">
                                            Requested by: {{ $notification->data['requester_name'] ?? 'Admin' }}
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                                        @if(!$notification->read_at)
                                            <span class="badge badge-danger badge-pill">New</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ $notification->data['view_url'] ?? '#' }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    @if(!$notification->read_at)
                                        <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-notification-id="{{ $notification->id }}">
                                            <i class="fas fa-check"></i> Mark Read
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-bell fa-2x text-gray-300 mb-2"></i>
                            <p class="text-gray-500 mb-0">No recent notifications</p>
                        </div>
                    @endif
                </div>
            </div>


            <!-- Status Summary -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm">Pending</span>
                            <span class="text-sm font-weight-bold">{{ $statusSummary['pending'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ $statusSummary['pending_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm">Flagged</span>
                            <span class="text-sm font-weight-bold">{{ $statusSummary['flagged'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-danger" style="width: {{ $statusSummary['flagged_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
    
    // Mark notification as read
    $('.mark-read-btn').click(function() {
        const notificationId = $(this).data('notification-id');
        const button = $(this);
        
        $.post(`/notifications/${notificationId}/mark-as-read`, {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                button.remove();
                $(`.badge[data-notification-id="${notificationId}"]`).remove();
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to mark notification as read:', error);
        });
    });
});
</script>
@endpush
@endsection