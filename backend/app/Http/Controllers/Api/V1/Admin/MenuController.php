<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuTreeService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function tree(MenuTreeService $service): JsonResponse
    {
        $this->authorize('viewAny', MenuItem::class);

        $menu = Menu::firstOrFail();

        return response()->success($service->getTree($menu));
    }
}
