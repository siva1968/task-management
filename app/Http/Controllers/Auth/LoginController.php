<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Attempt to log the user in
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account has been deactivated.',
                ]);
            }

            // Check if user's tenant is active
            if (!$user->tenant || !$user->tenant->isActive()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your organization account is not active.',
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
