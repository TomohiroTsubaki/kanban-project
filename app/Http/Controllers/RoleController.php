<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $pageTitle = 'Role Lists';
        $roles = Role::all();

        return view('roles.index', [
            'pageTitle' => $pageTitle,
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $pageTitle = 'Add Role';
        $permissions = Permission::all();
        return view('roles.create', [
            'pageTitle' => $pageTitle,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'permissionIds' => ['required'],
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
            ]);

            $role->permissions()->sync($request->permissionIds);

            DB::commit();

            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Role';
        $role = Role::find($id);
        $permissions = Permission::all();

        $this->authorize('editAnyRole', Role::class);

        return view('roles.edit', [
            'pageTitle' => $pageTitle,
            'permissions' => $permissions,
            'role' => $role,
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'permissionIds' => ['required'],
        ]);

        $this->authorize('editAnyRole', Role::class);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            $role->update([
                'name' => $request->name,
            ]);
            $role->permissions()->sync($request->permissionIds);
            DB::commit();
            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        $title = 'Role Delete Page';
        $role = Role::findOrFail($id);

        $this->authorize('deleteAnyRole', Role::class);

        return view('roles.delete', ['pageTitle' => $title, 'role' => $role]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $this->authorize('deleteAnyRole', Role::class);

        if (count($role->users) > 0) {
            return back()->with(
                'message-error',
                'Role still has a user. You can\'t delete it.'
            );
        }
        $role->delete();
        return redirect()->route('roles.index');
    }
}
