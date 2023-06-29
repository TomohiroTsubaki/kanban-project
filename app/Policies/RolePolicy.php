<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    protected function getUserPermissions(User $user)
    {
        return $user
            ->role()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');
    }

    public function before(User $user)
    {
        if ($user->role && $user->role->name == 'admin') {
            return true;
        }

        return null;
    }

    public function viewAnyRole($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('view-any-roles')) {
            return true;
        }

        return false;
    }

    public function editAnyRole($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('edit-any-roles')) {
            return true;
        }

        return false;
    }

    public function deleteAnyRole($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('delete-any-roles')) {
            return true;
        }

        return false;
    }
}
