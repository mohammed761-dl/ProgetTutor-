<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    /* this is admin auth fonction login logout */
    public function AdminLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->intended('/admin/dashboard');
        }
        
        return Inertia::render('Auth/AdminLogin', [
            'status' => session('status'),
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object)[],
        ]);
    }

    public function AdminstoreLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended('/admin/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function AdminLogout(Request $request)
    {
        Auth::guard('admin')->logout();
        
        // Clear and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Clear any remember me cookies
        \Cookie::forget('admin_remember');
        
        return redirect()->route('serp-admin-login')->with('status', 'Logged out successfully');
    }

    /* ------------------this is user auth fonction login logout and registrin-------------- */

    public function UserLogin()
    {
        return Inertia::render('Auth/UserLogin');
    }

    // public function Usersignup()
    // {
    //     return Inertia::render("Auth/UserRegisterin");
    // }

    public function UserstoreLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        // Use web guard for regular users
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Provided credentials do not match.',
        ]);
    }

    // public function UserstoreSignup(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => ['required','string','max:255'],
    //         'email' => ['required','string','email','max:255','unique:users'],
    //         'password' => ['required','string','min:8','confirmed'],
    //         'password_confirmation' => ['required','string','min:8'],

    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator)->withInput();
    //     }

    //     $User = User::create(
    //         [
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //         ]
    //     );

    //     return redirect()->route('login');
    // }

    public function Userlogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }
}
