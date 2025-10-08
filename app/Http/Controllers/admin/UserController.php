<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
class UserController extends Controller
{
    /*public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }*/

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with('roles')->select('users.*');

            return DataTables::of($data)
                ->addColumn('roles', function ($user) {
                    return $user->roles->map(function ($role) {
                        return '<span class="badge bg-info text-dark">' . e($role->name) . '</span>';
                    })->implode(' ');
                })
                ->addColumn('actions', function ($user) {
                    return view('admin.users.partials.actions', compact('user'))->render();
                })
                ->rawColumns(['roles', 'actions'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name');
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required'
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name');
        $userRole = $user->roles->pluck('name')->first();
        return view('admin.users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'role'     => 'required'
        ]);

        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'] ? Hash::make($data['password']) : $user->password,
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }
}
