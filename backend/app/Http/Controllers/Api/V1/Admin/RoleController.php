<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRolePrivilegesRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->orderBy('name')->get();

        return response()->success(RoleResource::collection($roles));
    }

    public function syncPrivileges(SyncRolePrivilegesRequest $request, Role $role): JsonResponse
    {
        $role->syncPermissions($request->validated('privileges', []));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->success(new RoleResource($role->fresh('permissions')), 'Role privileges updated.');
    }
}
