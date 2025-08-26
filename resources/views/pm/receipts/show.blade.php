@extends('app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Receipt Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Liquidated Forms
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Receipt Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Receipt Information</h6>
                    <span class="badge badge-{{ $receipt->status_badge_color }} badge-lg">
                        {{ $receipt->formatted_status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Receipt Number:</strong></td>
                                    <td>{{ $receipt->receipt_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $receipt->vendor_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong>{{ $receipt->formatted_amount }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Receipt Date:</strong></td>
                                    <td>{{ $receipt->formatted_receipt_date }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><span class="badge badge-info">{{ ucfirst($receipt->receipt_type) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded By:</strong></td>
                                    <td>
                                        @if($receipt->uploader)
                                            {{ $receipt->uploader->full_name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Uploaded At:</strong></td>
                                    <td>{{ $receipt->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $receipt->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($receipt->description)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Description:</h6>
                            <p class="text-muted">{{ $receipt->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($receipt->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="font-weight-bold">Notes:</h6>
                            <p class="text-muted">{{ $receipt->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- File Information -->
            @if($receipt->file_path)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">File Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Original Filename:</strong></td>
                                    <td>{{ $receipt->original_file_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>File Size:</strong></td>
                                    <td>{{ $receipt->formatted_file_size }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <a href="{{ route('pm.receipts.download', $receipt) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-download"></i> Download Receipt
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($receipt->file_path)
                            <a href="{{ route('pm.receipts.download', $receipt) }}" 
                               class="btn btn-success">
                                <i class="fas fa-download"></i> Download Receipt
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Report Card -->
            @if($receipt->financialReport)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Linked Financial Report</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Report Title:</strong><br>
                        {{ $receipt->financialReport->title }}
                    </div>
                    <div class="mb-3">
                        <strong>Period:</strong><br>
                        {{ $receipt->financialReport->formatted_period }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $receipt->financialReport->status_badge_color }}">
                            {{ $receipt->financialReport->formatted_status }}
                        </span>
                    </div>
                    <a href="{{ route('pm.financial-reports.show', $receipt->financialReport) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i> View Financial Report
                    </a>
                </div>
            </div>
            @endif

            <!-- Liquidated Form Card -->
            @if($receipt->liquidatedForm)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Linked Liquidated Form</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Form Number:</strong> {{ $receipt->liquidatedForm->form_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong> 
                        <span class="badge badge-{{ $receipt->liquidatedForm->status_badge_color }}">
                            {{ $receipt->liquidatedForm->formatted_status }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Liquidation Date:</strong> {{ $receipt->liquidatedForm->liquidation_date }}
                    </div>
                    <a href="{{ route('pm.liquidated-forms.show', $receipt->liquidatedForm) }}" 
                       class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-eye"></i> View Liquidated Form
                    </a>
                </div>
            </div>
            @endif

            <!-- Quick Links Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pm.liquidated-forms.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Liquidated Forms
                        </a>
                        @if($receipt->financialReport && $receipt->financialReport->project)
                            <a href="{{ route('projects.show', $receipt->financialReport->project) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-project-diagram"></i> View Project
                            </a>
                        @endif
                        <a href="{{ route('pm.expenditures.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-money-bill"></i> Manage Expenditures
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
