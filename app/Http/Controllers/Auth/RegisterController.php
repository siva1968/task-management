<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|alpha_dash|unique:tenants,subdomain',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // Create tenant
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'subdomain' => Str::lower($request->subdomain),
                'subscription_tier' => 'basic',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14), // 14-day trial
            ]);

            // Get admin role
            $adminRole = Role::where('slug', 'admin')->first();

            // Create admin user for this tenant
            $user = User::create([
                'tenant_id' => $tenant->id,
                'role_id' => $adminRole->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            DB::commit();

            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been created successfully. Your trial period is 14 days.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }
}
