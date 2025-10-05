<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\CompanyInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display admin profile with company info
     */
    public function profile()
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect('/serp-admin-login')->withErrors(['error' => 'Please login to access your profile.']);
        }

        // Get company info using singleton pattern
        $companyInfo = CompanyInfo::getInstance();

        Log::info('Admin profile accessed: '.$admin->email);

        return Inertia::render('AdminProfile/Index', [
            'admin' => [
                'id_admin' => $admin->id_admin,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
                'created_at' => $admin->created_at,
            ],
            'companyInfo' => $companyInfo,
        ]);
    }

    /**
     * Update company information
     */
    public function updateCompanyInfo(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect('/serp-admin-login')->withErrors(['error' => 'Please login to update company info.']);
        }

        Log::info('Admin updating company info: '.$admin->email);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'warranty_duration' => 'required|integer|min:1|max:120',
            'support_info' => 'required|string|max:1000',
            'bank_name' => 'required|string|max:255',
            'swift_bic' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'iban' => 'required|string|max:50',
            'authorized_name' => 'required|string|max:255',
            'authorized_title' => 'required|string|max:255',
            'signature_email' => 'required|email|max:255',
            'signature_phone' => 'required|string|max:50',
            'general_conditions_url' => 'nullable|url|max:255',
            'vat_number' => 'required|string|max:50',
        ]);

        $companyInfo = CompanyInfo::getInstance();
        $companyInfo->update($validated);

        Log::info('Company info updated successfully by: '.$admin->name);

        return redirect('/Profile')
            ->with('success', 'Company information updated successfully.');
    }

    /**
     * Update admin profile
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect('/serp-admin-login')->withErrors(['error' => 'Please login to update your profile.']);
        }

        Log::info('Admin updating profile: '.$admin->email);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id_admin, 'id_admin')],
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        Log::info('Admin profile updated successfully: '.$admin->name);

        return redirect('/Profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return redirect('/serp-admin-login')->withErrors(['error' => 'Please login to change your password.']);
        }

        Log::info('Admin changing password: '.$admin->email);

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        Log::info('Admin password changed successfully: '.$admin->name);

        return redirect('/Profile')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * List all admins
     */
    public function index() {}

    /**
     * Show create form
     */
    public function create() {}

    /**
     * Store new admin
     */
    public function store(Request $request) {}

    /**
     * Show admin details
     */
    public function show(Admin $admin) {}

    /**
     * Show edit form
     */
    public function edit(Admin $admin) {}

    /**
     * Update admin
     */
    public function update(Request $request, Admin $admin) {}

    /**
     * Delete admin
     */
    public function destroy(Admin $admin) {}
}
