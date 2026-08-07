<?php

namespace App\Enums;

enum AppModules: string
{
    case Users = 'users';
    case Menus = 'menus';
    case Pages = 'pages';
    case Roles = 'roles';
    case Priviledges = 'permissions';
    case ActivityLog = 'activity-log';

    public function label(): string
    {
        return str_replace('-', ' ', $this->value);
    }
}
