@php
    $statusColors = [
        'pending' => 'warning',
        'under_review' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'flagged' => 'danger',
        'clarification_requested' => 'warning',
        'revision_requested' => 'warning',
        'processed' => 'secondary'
    ];
    
    $statusColor = $statusColors[$status] ?? 'secondary';
@endphp

<span class="badge badge-{{ $statusColor }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
