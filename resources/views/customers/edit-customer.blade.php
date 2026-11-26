@extends('layouts.app')

@section('title', 'Update Customer')

@section('content')
    <div class="card">
        <div class="header-row">
            <h1>Update Customer</h1>
            <a href="{{ route('customer-list') }}" class="btn btn-secondary">Back</a>
        </div>

        <form action="{{ route('update-customer', $customer) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control" minlength="2" maxlength="100"
                    value="{{ old('name', $customer->name) }}" required>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" class="form-control" minlength="12"
                    maxlength="20" value="{{ old('phone_number', $customer->phone_number) }}" required>
                @error('phone_number')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <div data-preview-wrapper style="margin-bottom: 8px; {{ $customer->profile_picture ? '' : 'display: none;' }}">
                    <img id="profile_picture_preview"
                        src="{{ $customer->profile_picture ? asset('storage/' . $customer->profile_picture) : '' }}"
                        alt="profile_picture" width="150">
                </div>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                    value="{{ old('profile_picture', $customer->profile_picture) }}" data-preview="profile_picture_preview">
                @error('profile_picture')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('customer-list') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
