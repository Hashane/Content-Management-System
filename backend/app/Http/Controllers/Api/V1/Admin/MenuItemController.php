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
    public function store(StoreMenuItemRequest $request, Menu $menu): JsonResponse
    {
        $data = $request->validated();
        $data['menu_id'] = $menu->id;
        $data['position'] = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('position');
        $data['position'] = $data['position'] === null ? 0 : $data['position'] + 1;

        $item = MenuItem::create($data);

        return response()->success(new MenuItemResource($item), 'Menu item created.', 201);
    }

    public function update(UpdateMenuItemRequest $request, Menu $menu, MenuItem $item): JsonResponse
    {
        abort_if($item->menu_id !== $menu->id, 404);

        $item->update($request->validated());

        return response()->success(new MenuItemResource($item->fresh()), 'Menu item updated.');
    }

    public function destroy(Menu $menu, MenuItem $item): JsonResponse
    {
        abort_if($item->menu_id !== $menu->id, 404);

        $this->authorize('delete', $item);

        $item->delete();

        return response()->success(null, 'Menu item deleted.');
    }

    public function move(MoveMenuItemRequest $request, Menu $menu, MenuItem $item, MenuTreeService $service): JsonResponse
    {
        abort_if($item->menu_id !== $menu->id, 404);

        $tree = $service->move($menu, $item, $request->validated('parent_id'), $request->validated('position'));

        return response()->success($tree, 'Menu item moved.');
    }
}
