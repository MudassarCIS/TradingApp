@extends('layouts.admin-layout')
@section('title', 'Roles Edit')
@section('content')
    <div class="container">
        <h2>Edit Permissions for Role: {{ ucfirst($role->name) }}</h2>
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               class="form-check-input"
                            {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
            <button class="btn btn-success">Update</button>
        </form>
    </div>
@endsection
