@if($liquidatedForm->financialReport)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Financial Report Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Report Title:</strong></td>
                        <td>{{ $liquidatedForm->financialReport->title }}</td>
                    </tr>
                    <tr>
                        <td><strong>Report Period:</strong></td>
                        <td>{{ $liquidatedForm->financialReport->formatted_period }}</td>
                    </tr>
                    <tr>
                        <td><strong>Report Status:</strong></td>
                        <td>
                            <span class="badge badge-{{ $liquidatedForm->financialReport->status_badge_color }}">
                                {{ $liquidatedForm->financialReport->formatted_status }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Total Expenditures:</strong></td>
                        <td class="font-weight-bold text-success">{{ $liquidatedForm->financialReport->formatted_total_expenditures }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Receipts:</strong></td>
                        <td class="font-weight-bold text-info">{{ $liquidatedForm->financialReport->formatted_total_receipts }}</td>
                    </tr>
                    <tr>
                        <td><strong>Variance:</strong></td>
                        <td class="font-weight-bold {{ $liquidatedForm->financialReport->is_variance_positive ? 'text-success' : 'text-danger' }}">
                            {{ $liquidatedForm->financialReport->formatted_variance_amount }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="{{ route('finance.financial-reports.show', $liquidatedForm->financialReport) }}" 
               class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> View Financial Report
            </a>
        </div>
    </div>
</div>
@endif
