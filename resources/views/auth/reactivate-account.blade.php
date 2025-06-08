@extends('app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivate Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-green-600">Reactivate Account</h2>
            <p class="text-gray-600 mt-2">Enter your credentials to reactivate your deactivated account.</p>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Account Reactivation</h3>
            <p class="text-sm text-blue-700">
                Once reactivated, you'll be able to log in normally and access all your account features.
            </p>
        </div>

        <form method="POST" action="{{ route('account.reactivate') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('email') border-red-500 @enderror"
                    placeholder="Enter your email address"
                >
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    placeholder="Enter your password"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-200 font-medium"
            >
                Reactivate Account
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
@endsection