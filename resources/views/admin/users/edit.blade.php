@extends('layouts.admin-layout')
@section('title', 'Users Edit')
@section('content')
<div class="container">
    <h2>Edit User</h2>
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required/>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required/>
        </div>
        <div class="mb-3">
            <label>Password (leave blank if unchanged)</label>
            <input type="password" name="password" class="form-control"/>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control"/>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" {{ $role == $userRole ? 'selected' : '' }}>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-success">Update</button>
    </form>
</div>
@endsection
