<?php

namespace Database\Seeders;

use App\Enums\AppModules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];

        foreach (AppModules::cases() as $module) {
            $actions = ['list', 'add', 'update', 'delete'];

            foreach ($actions as $action) {
                $permissions[] = ['name' => "{$module->value}.{$action}", 'guard_name' => 'web'];
            }
        }

        Permission::upsert($permissions, ['name', 'guard_name']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
