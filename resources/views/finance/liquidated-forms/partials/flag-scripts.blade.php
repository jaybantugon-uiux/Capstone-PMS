@push('scripts')
<script>
$(document).ready(function() {
    // Flag button
    $('#flagBtn').click(function() {
        $('#flagModal').modal('show');
    });

    // Unflag button
    $('#unflagBtn').click(function() {
        if (confirm('Are you sure you want to remove the flag from this form?')) {
            $.post(`/finance/liquidated-forms/{{ $liquidatedForm->id }}/unflag`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('An error occurred while removing the flag.');
            });
        }
    });

    // Flag form submission
    $('#flagForm').submit(function(e) {
        e.preventDefault();
        const reason = $('#flag_reason').val();
        const priority = $('#flag_priority').val();

        if (!reason.trim()) {
            alert('Please provide a reason for flagging.');
            return;
        }

        $.post(`/finance/liquidated-forms/{{ $liquidatedForm->id }}/flag`, {
            _token: '{{ csrf_token() }}',
            flag_reason: reason,
            flag_priority: priority
        })
        .done(function(response) {
            if (response.success) {
                $('#flagModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function() {
            alert('An error occurred while flagging the form.');
        });
    });

    // Clear form when modal is hidden
    $('#flagModal').on('hidden.bs.modal', function() {
        $('#flag_reason').val('');
        $('#flag_priority').val('medium');
    });
});
</script>
@endpush
