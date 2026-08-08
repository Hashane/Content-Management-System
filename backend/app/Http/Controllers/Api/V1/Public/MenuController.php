<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\MenuTreeService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __invoke(MenuTreeService $service): JsonResponse
    {
        $menu = Menu::where('slug', 'main-menu')->firstOrFail();

        return response()->success($service->getPublicTree($menu));
    }
}
