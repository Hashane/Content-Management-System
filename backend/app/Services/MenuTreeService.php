<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuTreeService
{
    /**
     * Move a single item to a new parent/position, adjusting sibling
     * positions to make room.
     *
     * @return array<int, array<string, mixed>>
     */
    public function move(Menu $menu, MenuItem $item, ?int $parentId, int $position): array
    {
        DB::transaction(function () use ($menu, $item, $parentId, $position) {
            MenuItem::where('menu_id', $menu->id)
                ->where('parent_id', $item->parent_id)
                ->where('position', '>', $item->position)
                ->decrement('position');

            MenuItem::where('menu_id', $menu->id)
                ->where('parent_id', $parentId)
                ->whereKeyNot($item->id)
                ->where('position', '>=', $position)
                ->increment('position');

            $item->update([
                'parent_id' => $parentId,
                'position' => $position,
            ]);
        });

        return $this->getTree($menu);
    }

    /**
     * Build the current nested tree for a menu from a single flat query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTree(Menu $menu): array
    {
        $items = MenuItem::where('menu_id', $menu->id)->orderBy('position')->get();

        return $this->buildTree($items, null);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $items, ?int $parentId): array
    {
        return $items->where('parent_id', $parentId)
            ->values()
            ->map(fn (MenuItem $item) => [
                'id' => $item->id,
                'label' => $item->label,
                'item_type' => $item->item_type,
                'page_id' => $item->page_id,
                'position' => $item->position,
                'children' => $this->buildTree($items, $item->id),
            ])
            ->all();
    }
}
