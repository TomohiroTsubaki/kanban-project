<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $pageTitle = 'Users List';
        $this->authorize('viewUserRole', User::class);
        $users = User::all();
        return view('users.index', [
            'users' => $users,
            'pageTitle' => $pageTitle,
        ]);
    }

    public function editRole($id)
    {
        $pageTitle = 'Edit User Role';
        $user = User::findOrFail($id);
        $roles = Role::all();

        $this->authorize('manageUserRole', User::class);

        return view('users.edit_role', [
            'pageTitle' => $pageTitle,
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRole($id, Request $request)
    {
        $user = User::findOrFail($id);

        $this->authorize('manageUserRole', User::class);

        $user->update([
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('users.index');
    }
}
