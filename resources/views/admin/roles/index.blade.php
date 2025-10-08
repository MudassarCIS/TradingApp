@extends('layouts.admin-layout')
@section('title', 'Roles Mgm')
@section('content')
    <div class="container">
        <h2>Roles</h2>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Role</th><th>Permissions</th><th>Actions</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->permissions->pluck('name')->implode(', ') }}</td>
                    <td>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">Edit Permissions</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
