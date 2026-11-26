@extends('layouts.app')

@section('title', 'Update Service')

@section('content')
    <div class="card">
        <div class="header-row">
            <h1>Update Service</h1>
            <a href="{{ route('service-list') }}" class="btn btn-secondary">Back</a>
        </div>

        <form action="{{ route('update-service', $service) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control" minlength="2" maxlength="100"
                    value="{{ old('name', $service->name) }}" required>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" maxlength="1000" required>{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <div data-preview-wrapper style="margin-bottom: 8px; {{ $service->image ? '' : 'display: none;' }}">
                    <img id="image_preview"
                        src="{{ $service->image ? asset('storage/' . $service->image) : '' }}"
                        alt="service_image" width="150">
                </div>
                <input type="file" id="image" name="image" accept="image/*"
                    value="{{ old('image', $service->image) }}" data-preview="image_preview">
                @error('image')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('service-list') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
