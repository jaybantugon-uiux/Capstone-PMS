@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-check me-2"></i>
                        Reactivate Account
                    </h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Account Reactivation
                        </h5>
                        <p class="mb-0">
                            If you previously deactivated your account, you can reactivate it here by providing your email address and password.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('account.reactivate') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <strong>Email Address</strong>
                            </label>
                            <input 
                                id="email" 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autocomplete="email" 
                                autofocus
                                placeholder="Enter your email address"
                            >
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <strong>Password</strong>
                            </label>
                            <input 
                                id="password" 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-check me-2"></i>
                                Reactivate My Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2">Remember your account details?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Back to Login
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <p class="mb-2">Forgot your password?</p>
                        <a href="{{ route('password.request') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-key me-1"></i>
                            Reset Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Need Help?
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Can't reactivate your account?</strong></p>
                    <ul>
                        <li>Make sure you're using the correct email address and password</li>
                        <li>If you forgot your password, use the "Reset Password" link above</li>
                        <li>If you're still having trouble, contact support</li>
                    </ul>
                    
                    <p class="mt-3"><strong>What happens when you reactivate?</strong></p>
                    <ul>
                        <li>Your account status will be changed back to active</li>
                        <li>You'll be able to log in normally</li>
                        <li>All your account data will be restored</li>
                        <li>You'll be redirected to login after successful reactivation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection