<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

         $permissions = [
            'create projects',
            'edit projects',
            'delete projects',
            'archive projects',
            'view projects',

            'manage members',      // full — add/remove/change roles
            'add members',         // limited — only add/remove, no role change

            'create tasks',
            'edit any task',       // edit any task in project
            'edit assigned task',  // only edit tasks assigned to you
            'delete tasks',
            'assign tasks',
            'update task status',  // members can only update status

            'add comments',
            'upload attachments',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'api']);
        $managerRole->givePermissionTo([
            'create projects',
            'edit projects',
            'delete projects',
            'archive projects',
            'view projects',
            'manage members',
            'create tasks',
            'edit any task',
            'delete tasks',
            'assign tasks',
            'add comments',
            'upload attachments',
            'view reports',
        ]);

        $teamLeaderRole = Role::create(['name' => 'team-leader', 'guard_name' => 'api']);
        $teamLeaderRole->givePermissionTo([
            'edit projects',
            'view projects',
            'add members',
            'create tasks',
            'edit any task',
            'delete tasks',
            'assign tasks',
            'add comments',
            'upload attachments',
        ]);

        $memberRole = Role::create(['name' => 'member', 'guard_name' => 'api']);
        $memberRole->givePermissionTo([
            'view projects',
            'create tasks',
            'edit assigned task',
            'update task status',
            'add comments',
            'upload attachments',
        ]);
    }
}
