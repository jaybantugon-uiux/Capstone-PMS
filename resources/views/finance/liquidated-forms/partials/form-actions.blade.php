@php
    $canEdit = $liquidatedForm->canBeEdited();
    $isFlagged = $liquidatedForm->status === 'flagged';
@endphp

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
            
            @if($canEdit)
            <a href="{{ route('finance.liquidated-forms.edit', $liquidatedForm) }}" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit Form
            </a>
            @endif
            
            @if(!$isFlagged)
            <button type="button" class="btn btn-danger btn-sm" id="flagBtn">
                <i class="fas fa-flag"></i> Flag for Review
            </button>
            @else
            <button type="button" class="btn btn-success btn-sm" id="unflagBtn">
                <i class="fas fa-flag-checkered"></i> Remove Flag
            </button>
            @endif
        </div>
    </div>
</div>
