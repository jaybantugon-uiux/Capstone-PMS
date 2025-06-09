@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Deactivate Account
                    </h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-warning me-2"></i>
                            Warning: This action is serious!
                        </h5>
                        <p class="mb-0">Deactivating your account will:</p>
                        <ul class="mt-2 mb-0">
                            <li>Log you out of all devices</li>
                            <li>Revoke all your access tokens</li>
                            <li>Prevent you from logging in until reactivation</li>
                            <li>Make your account temporarily inaccessible</li>
                        </ul>
                        <hr>
                        <p class="mb-0">
                            <strong>Note:</strong> You can reactivate your account later using your email and password.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.deactivate') }}" id="deactivateForm">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <strong>Confirm Your Password</strong>
                            </label>
                            <input 
                                id="password" 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter your current password"
                            >
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="confirmation" class="form-label">
                                <strong>Type "DEACTIVATE" to confirm</strong>
                            </label>
                            <input 
                                id="confirmation" 
                                type="text" 
                                class="form-control @error('confirmation') is-invalid @enderror" 
                                name="confirmation" 
                                required 
                                placeholder="Type DEACTIVATE to confirm"
                                style="text-transform: uppercase;"
                            >
                            @error('confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">
                                You must type "DEACTIVATE" exactly as shown to confirm this action.
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="btn btn-danger"
                                id="deactivateBtn"
                                disabled
                            >
                                <i class="fas fa-user-times me-1"></i>
                                Deactivate My Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Your Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                            <p><strong>Username:</strong> {{ auth()->user()->username }}</p>
                            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-success">{{ ucfirst(auth()->user()->status) }}</span>
                            </p>
                            <p><strong>Member Since:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('confirmation');
    const deactivateBtn = document.getElementById('deactivateBtn');
    const form = document.getElementById('deactivateForm');

    // Convert input to uppercase
    confirmationInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        checkFormValidity();
    });

    passwordInput.addEventListener('input', checkFormValidity);

    function checkFormValidity() {
        const hasPassword = passwordInput.value.length > 0;
        const hasCorrectConfirmation = confirmationInput.value === 'DEACTIVATE';
        
        deactivateBtn.disabled = !(hasPassword && hasCorrectConfirmation);
    }

    // Double confirmation before submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you absolutely sure you want to deactivate your account? This will log you out immediately.')) {
            if (confirm('This is your final warning. Click OK to proceed with account deactivation.')) {
                this.submit();
            }
        }
    });
});
</script>
@endsection