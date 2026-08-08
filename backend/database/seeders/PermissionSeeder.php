<?php

namespace Database\Seeders;

use App\Enums\AppModules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private const MODULE_ACTIONS = [
        'pages' => ['list', 'create', 'update', 'delete'],
        'menus' => ['manage'],
        'users' => ['list', 'create', 'update', 'delete'],
        'roles' => ['list', 'create', 'update', 'delete'],
        'privileges' => ['list', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];

        foreach (AppModules::cases() as $module) {
            foreach (self::MODULE_ACTIONS[$module->value] as $action) {
                $permissions[] = ['name' => "{$module->value}.{$action}", 'guard_name' => 'web'];
            }
        }

        Permission::upsert($permissions, ['name', 'guard_name']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
