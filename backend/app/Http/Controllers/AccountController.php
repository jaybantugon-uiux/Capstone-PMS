<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AccountController extends Controller
{
    // Show the account edit form
    public function edit()
    {
        $user = User::find(Auth::id());
        return view('account.edit', compact('user'));
    }

    // Handle the profile update
    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        // Validate input
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Update user info
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Log the user out
        Auth::logout();

        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'Profile updated successfully! Please log in again.');
    }
}