@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="card">
        <div class="header-row">
            <h1>Customers</h1>
            @auth
                <a href="{{ route('create-customer') }}" class="btn btn-primary">+ Create Customer</a>
            @endauth
        </div>

        <form action="{{ route('customer-list') }}" method="GET">
            <div class="form-group">
                <label for="search">Search</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    class="form-control"
                    minlength="2"
                    maxlength="50"
                    value="{{ request('search') }}"
                >
                @error('search')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Profile Picture</th>
                    @auth
                        <th>Actions</th>
                    @endauth
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone_number ?? '-' }}</td>
                        <td>
                            @if ($customer->profile_picture)
                                <img src="{{ asset('storage/' . $customer->profile_picture) }}" alt="profile_picture" width="150">
                            @endif
                        </td>
                        @auth
                            <td>
                                <div class="actions">
                                    <a href="{{ route('edit-customer', $customer) }}" class="btn btn-secondary">Update</a>
                                    <form action="{{ route('delete-customer', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        @endauth
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() ? 5 : 4 }}" class="empty-state">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $customers->links() }}
        </div>
    </div>
@endsection
