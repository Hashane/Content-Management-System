<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrivilegeResource;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PrivilegeController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $privileges = Permission::orderBy('name')->get();

        return response()->success(PrivilegeResource::collection($privileges));
    }
}
