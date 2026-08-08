<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveMenuItemRequest;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuTreeService;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menu = Menu::firstOrFail();

        $data = $request->validated();
        $data['menu_id'] = $menu->id;
        $data['position'] = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position');
        $data['position'] = $data['position'] === null ? 0 : $data['position'] + 1;

        $item = MenuItem::create($data);

        return response()->success(new MenuItemResource($item), 'Menu item created.', 201);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $item): JsonResponse
    {
        $item->update($request->validated());

        return response()->success(new MenuItemResource($item->fresh()), 'Menu item updated.');
    }

    public function destroy(MenuItem $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return response()->success(null, 'Menu item deleted.');
    }

    public function move(MoveMenuItemRequest $request, MenuItem $item, MenuTreeService $service): JsonResponse
    {
        $menu = Menu::firstOrFail();

        $tree = $service->move($menu, $item, $request->validated('parent_id'), $request->validated('position'));

        return response()->success($tree, 'Menu item moved.');
    }
}
