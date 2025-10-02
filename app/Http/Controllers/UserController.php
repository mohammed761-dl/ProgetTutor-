<?php

// filepath: app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::select('id_user as id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Users fetched: '.$users->count());

            return Inertia::render('Users/Index', [
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users: '.$e->getMessage());

            return Inertia::render('Users/Index', [
                'users' => [],
            ]);
        }
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => ['CEO', 'Commercial', 'Viewer'],
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'role' => ['required', Rule::in(['CEO', 'Commercial', 'Viewer'])],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            Log::info('User created: '.$user->email);

            return redirect('/User')
                ->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating user: '.$e->getMessage());

            return back()->withErrors(['error' => 'Failed to create user: '.$e->getMessage()]);
        }
    }

    // ✅ Fix: Use route model binding with User object
    public function edit(User $user)
    {
        try {
            Log::info('Editing user: '.$user->id_user);

            return Inertia::render('Users/Edit', [
                'user' => $user,
                'roles' => ['CEO', 'Commercial', 'Viewer'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error finding user: '.$e->getMessage());

            return redirect('/User')->withErrors(['error' => 'User not found.']);
        }
    }

    // ✅ Fix: Use route model binding with User object
    public function update(Request $request, User $user)
    {
        try {
            Log::info('Updating user: '.$user->id_user);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id_user, 'id_user')],
                'password' => 'nullable|string|min:8|confirmed',
                'role' => ['required', Rule::in(['CEO', 'Commercial', 'Viewer'])],
            ]);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            Log::info('User updated successfully: '.$user->name);

            return redirect('/User')
                ->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating user: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to update user: '.$e->getMessage()]);
        }
    }

    // ✅ Fix: Use route model binding with User object
    public function destroy(User $user)
    {
        try {
            $userName = $user->name;
            $user->delete();

            Log::info('User deleted: '.$userName);

            return redirect('/User')
                ->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting user: '.$e->getMessage());

            return back()->withErrors(['error' => 'Failed to delete user: '.$e->getMessage()]);
        }
    }
}
