<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin user management page.
     */
    public function index(): View
    {
        $users = User::query()
            ->latest()
            ->paginate(10);

        return view('admin.index', compact('users'));
    }

    /**
     * Store a newly created user account.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:user,admin'],
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->forceFill([
            'role' => $validated['role'],
        ])->save();

        return redirect()
            ->route('admin.index')
            ->with('status', 'User created successfully.');
    }
}
