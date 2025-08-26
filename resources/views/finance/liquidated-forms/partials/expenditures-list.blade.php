@if($liquidatedForm->expenditures && $liquidatedForm->expenditures->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Linked Expenditures</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Expenditure</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidatedForm->expenditures as $expenditure)
                    <tr>
                        <td>
                            <div>
                                <div class="font-weight-bold">{{ Str::limit($expenditure->purpose, 40) }}</div>
                                <small class="text-muted">Submitted by {{ $expenditure->submitter->first_name ?? 'N/A' }} {{ $expenditure->submitter->last_name ?? '' }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                {{ $expenditure->category ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div class="font-weight-bold text-success">₱{{ number_format($expenditure->amount, 2) }}</div>
                        </td>
                        <td>
                            <div>
                                <div>{{ $expenditure->expense_date->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $expenditure->created_at->diffForHumans() }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $expenditure->status === 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($expenditure->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('finance.expenditures.show', $expenditure) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($expenditure->receipts && $expenditure->receipts->count() > 0)
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        data-toggle="tooltip" title="{{ $expenditure->receipts->count() }} receipt(s) attached">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                @endif
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
