<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $moderator->syncPermissions([
            'pages.list',
            'pages.create',
            'pages.update',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
