@extends('layouts.app')

@section('title', 'Admin Panel')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="header-row">
            <h1>Admin Panel &mdash; Add New User</h1>
        </div>

        @auth
            <p class="muted-text" style="margin-top: 0; margin-bottom: 1rem;">
                Welcome, {{ auth()->user()->name }}
            </p>
        @endauth

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    minlength="2"
                    maxlength="100"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    maxlength="255"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    minlength="6"
                    required
                >
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    minlength="6"
                    required
                >
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">+ Add User</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="header-row">
            <h1>Users</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
