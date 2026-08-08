<?php

namespace App\Services;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuTreeService
{
    /**
     * Public-facing tree for a menu. Page items whose linked page isn't
     * published and due are left out; groups are kept even if empty.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPublicTree(Menu $menu): array
    {
        $items = MenuItem::where('menu_id', $menu->id)
            ->with('page')
            ->orderBy('position')
            ->get();

        $visiblePageIds = Page::publishedAndDue()->pluck('id')->toArray();

        return $this->buildPublicTree($items, null, $visiblePageIds);
    }

    /**
     * @param  int[]  $visiblePageIds
     * @return array<int, array<string, mixed>>
     */
    private function buildPublicTree(Collection $items, ?int $parentId, array $visiblePageIds): array
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item->parent_id !== $parentId) {
                continue;
            }

            if ($item->item_type === MenuItemType::Page && ! in_array($item->page_id, $visiblePageIds)) {
                continue;
            }

            $tree[] = [
                'id' => $item->id,
                'label' => $item->label,
                'item_type' => $item->item_type,
                'page' => $item->page ? ['slug' => $item->page->slug, 'title' => $item->page->title] : null,
                'children' => $this->buildPublicTree($items, $item->id, $visiblePageIds),
            ];
        }

        return $tree;
    }

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
