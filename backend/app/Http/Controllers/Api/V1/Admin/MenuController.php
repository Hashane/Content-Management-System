<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Menu::class);

        $menus = Menu::query()->paginate($request->integer('per_page', 15));

        return response()->success(MenuResource::collection($menus)->response()->getData(true));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Menu::resolveUniqueSlug($data['slug'] ?? null, $data['name']);

        $menu = Menu::create($data);

        return response()->success(new MenuResource($menu), 'Menu created.', 201);
    }

    public function show(Menu $menu): JsonResponse
    {
        $this->authorize('view', $menu);

        return response()->success(new MenuResource($menu));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Menu::resolveUniqueSlug($data['slug'] ?? null, $data['name'], $menu->id);

        $menu->update($data);

        return response()->success(new MenuResource($menu->fresh()), 'Menu updated.');
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return response()->success(null, 'Menu deleted.');
    }

    public function restore(Menu $menu): JsonResponse
    {
        $this->authorize('restore', $menu);

        $menu->restore();

        return response()->success(new MenuResource($menu->fresh()), 'Menu restored.');
    }
}
