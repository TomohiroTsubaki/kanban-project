<?php

namespace App\Policies;

use App\Models\User;

class TaskPolicy
{
    protected function getUserPermissions($user)
    {
        return $user
            ->role()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');
    }

    public function before($user)
    {
        if ($user->role && $user->role->name == 'admin') {
            return true;
        }

        return null;
    }

    public function viewAnyTask($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('view-any-tasks')) {
            return true;
        }

        return false;
    }

    public function updateAnyTask($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('update-any-tasks')) {
            return true;
        }

        return false;
    }

    public function deleteAnyTask($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('delete-any-tasks')) {
            return true;
        }

        return false;
    }

    public function performAsTaskOwner($user, $task)
    {
        return $user->id == $task->user_id;
    }
}
