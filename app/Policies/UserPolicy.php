<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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

    public function viewUserRole($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('view-member-roles')) {
            return true;
        }

        return false;
    }

    public function manageUserRole(User $user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('manage-member-roles')) {
            return true;
        }

        return false;
    }
}
