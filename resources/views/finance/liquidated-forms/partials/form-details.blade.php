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
                        <td>@include('finance.liquidated-forms.partials.status-badge', ['status' => $liquidatedForm->status])</td>
                    </tr>
                    <tr>
                        <td><strong>Period Covered:</strong></td>
                        <td>{{ $liquidatedForm->period_covered_start->format('M d, Y') }} - {{ $liquidatedForm->period_covered_end->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Amount:</strong></td>
                        <td class="font-weight-bold text-success">₱{{ number_format($liquidatedForm->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Receipts:</strong></td>
                        <td class="font-weight-bold text-info">₱{{ number_format($liquidatedForm->total_receipts ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Variance:</strong></td>
                        <td class="font-weight-bold {{ ($liquidatedForm->variance_amount ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            ₱{{ number_format($liquidatedForm->variance_amount ?? 0, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($liquidatedForm->description)
        <div class="row mt-3">
            <div class="col-12">
                <h6><strong>Description:</strong></h6>
                <p class="text-muted">{{ $liquidatedForm->description }}</p>
            </div>
        </div>
        @endif

        @if($liquidatedForm->notes)
        <div class="row mt-3">
            <div class="col-12">
                <h6><strong>Notes:</strong></h6>
                <p class="text-muted">{{ $liquidatedForm->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
