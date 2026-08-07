<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('menu_items.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MenuItem $menuItem): bool
    {
        return $user->can('menu_items.list');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('menu_items.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MenuItem $menuItem): bool
    {
        return $user->can('menu_items.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MenuItem $menuItem): bool
    {
        return $user->can('menu_items.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MenuItem $menuItem): bool
    {
        return $user->can('menu_items.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MenuItem $menuItem): bool
    {
        return false;
    }
}
