<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = date('Y-m-d H:i:s', time());

        $permissions = [
            [
                'name' => 'view-any-tasks',
                'description' => 'View any tasks',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'update-any-tasks',
                'description' => 'Update any tasks',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'delete-any-tasks',
                'description' => 'Delete any tasks',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'view-any-roles',
                'description' => 'View any roles',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'create-new-roles',
                'description' => 'Create new roles',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'update-any-roles',
                'description' => 'Update any roles',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'delete-any-roles',
                'description' => 'Delete any roles',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'view-users-and-roles',
                'description' => 'View other users and their roles',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'manage-user-roles',
                'description' => 'Manage roles of users',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ];

        DB::table('permissions')->insert($permissions);
    }
}
