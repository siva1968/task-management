<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Task permissions
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'category' => 'tasks'],
            ['name' => 'Create Tasks', 'slug' => 'tasks.create', 'category' => 'tasks'],
            ['name' => 'Edit Tasks', 'slug' => 'tasks.edit', 'category' => 'tasks'],
            ['name' => 'Delete Tasks', 'slug' => 'tasks.delete', 'category' => 'tasks'],
            ['name' => 'Assign Tasks', 'slug' => 'tasks.assign', 'category' => 'tasks'],

            // Project permissions
            ['name' => 'View Projects', 'slug' => 'projects.view', 'category' => 'projects'],
            ['name' => 'Create Projects', 'slug' => 'projects.create', 'category' => 'projects'],
            ['name' => 'Edit Projects', 'slug' => 'projects.edit', 'category' => 'projects'],
            ['name' => 'Delete Projects', 'slug' => 'projects.delete', 'category' => 'projects'],

            // User permissions
            ['name' => 'View Users', 'slug' => 'users.view', 'category' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'category' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'category' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'category' => 'users'],
            ['name' => 'Manage Roles', 'slug' => 'users.manage-roles', 'category' => 'users'],

            // Report permissions
            ['name' => 'View Reports', 'slug' => 'reports.view', 'category' => 'reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'category' => 'reports'],

            // Settings permissions
            ['name' => 'View Settings', 'slug' => 'settings.view', 'category' => 'settings'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'category' => 'settings'],

            // Time tracking permissions
            ['name' => 'Log Time', 'slug' => 'time.log', 'category' => 'time'],
            ['name' => 'View All Time Logs', 'slug' => 'time.view-all', 'category' => 'time'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Full system access with all permissions',
            'is_system_role' => true,
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Can create and manage projects and tasks',
            'is_system_role' => true,
        ]);

        $teamMemberRole = Role::create([
            'name' => 'Team Member',
            'slug' => 'team_member',
            'description' => 'Can work on assigned tasks',
            'is_system_role' => true,
        ]);

        $clientViewerRole = Role::create([
            'name' => 'Client Viewer',
            'slug' => 'client_viewer',
            'description' => 'Read-only access to assigned projects',
            'is_system_role' => true,
        ]);

        // Assign permissions to Admin role (all permissions)
        $adminRole->permissions()->attach(Permission::all());

        // Assign permissions to Manager role
        $managerRole->permissions()->attach(
            Permission::whereIn('slug', [
                'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete', 'tasks.assign',
                'projects.view', 'projects.create', 'projects.edit',
                'users.view',
                'reports.view', 'reports.export',
                'time.log', 'time.view-all',
            ])->get()
        );

        // Assign permissions to Team Member role
        $teamMemberRole->permissions()->attach(
            Permission::whereIn('slug', [
                'tasks.view', 'tasks.edit',
                'projects.view',
                'time.log',
            ])->get()
        );

        // Assign permissions to Client Viewer role
        $clientViewerRole->permissions()->attach(
            Permission::whereIn('slug', [
                'tasks.view',
                'projects.view',
                'reports.view',
            ])->get()
        );

        $this->command->info('Roles and permissions created successfully!');
    }
}
