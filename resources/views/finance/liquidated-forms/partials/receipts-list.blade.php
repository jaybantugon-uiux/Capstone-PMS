@if($liquidatedForm->linkedReceipts && $liquidatedForm->linkedReceipts->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Receipts from Financial Report</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidatedForm->linkedReceipts as $receipt)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-2">
                                    @if($receipt->is_image)
                                        <i class="fas fa-image text-primary"></i>
                                    @elseif($receipt->is_pdf)
                                        <i class="fas fa-file-pdf text-danger"></i>
                                    @else
                                        <i class="fas fa-file text-secondary"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-weight-bold">{{ Str::limit($receipt->original_file_name, 30) }}</div>
                                    <small class="text-muted">{{ $receipt->formatted_file_size }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="font-weight-bold">{{ Str::limit($receipt->vendor_name, 25) }}</div>
                                @if($receipt->receipt_number)
                                    <small class="text-muted">#{{ $receipt->receipt_number }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="font-weight-bold text-success">{{ $receipt->formatted_amount }}</div>
                            @if($receipt->tax_amount > 0)
                                <small class="text-muted">Tax: {{ $receipt->formatted_tax_amount }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">
                                {{ \App\Models\Receipt::getReceiptTypeOptions()[$receipt->receipt_type] ?? $receipt->receipt_type }}
                            </span>
                        </td>
                        <td>
                            <div>
                                <div>{{ $receipt->formatted_receipt_date }}</div>
                                <small class="text-muted">{{ $receipt->created_at->diffForHumans() }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('finance.receipts.show', $receipt) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.receipts.download', $receipt) }}" 
                                   class="btn btn-sm btn-outline-success" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
