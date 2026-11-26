@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <div class="card">
        <div class="header-row">
            <h1>Services</h1>
            @auth
                <a href="{{ route('create-service') }}" class="btn btn-primary">+ Create Service</a>
            @endauth
        </div>

        <form action="{{ route('service-list') }}" method="GET">
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
                    <th>Description</th>
                    <th>Image</th>
                    @auth
                        <th>Actions</th>
                    @endauth
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>{{ $services->firstItem() + $loop->index }}</td>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->description }}</td>
                        <td>
                            @if ($service->image)
                                <img src="{{ asset('storage/' . $service->image) }}"
                                alt="service_image" width="150">
                            @endif
                        </td>
                        @auth
                            <td>
                                <div class="actions">
                                    <a href="{{ route('edit-service', $service) }}" class="btn btn-secondary">Update</a>
                                    <form action="{{ route('delete-service', $service) }}" method="POST" onsubmit="return confirm('Delete this service?')">
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
                        <td colspan="{{ auth()->check() ? 5 : 4 }}" class="empty-state">No services found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $services->links() }}
        </div>
    </div>
@endsection
