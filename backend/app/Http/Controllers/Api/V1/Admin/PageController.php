<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Pages\SavePageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PageIndexRequest;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function index(PageIndexRequest $request): JsonResponse
    {
        $pages = Page::query()
            ->with(['creator', 'updater'])
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('menu_id'), fn ($query) => $query->whereHas(
                'menuItems',
                fn ($menuItemQuery) => $menuItemQuery->where('menu_id', $request->integer('menu_id'))
            ))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->success(PageResource::collection($pages)->response()->getData(true));
    }

    public function store(StorePageRequest $request, SavePageAction $action): JsonResponse
    {
        $page = $action->handle($request->safe()->except(['cover_image']), $request->file('cover_image'));

        return response()->success(new PageResource($page->load(['creator', 'updater'])), 'Page created.', 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return response()->success(new PageResource($page->load(['creator', 'updater'])));
    }

    public function update(UpdatePageRequest $request, Page $page, SavePageAction $action): JsonResponse
    {
        $page = $action->handle($request->safe()->except(['cover_image']), $request->file('cover_image'), $page);

        return response()->success(new PageResource($page->fresh(['creator', 'updater'])), 'Page updated.');
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);

        $page->delete();

        return response()->success(null, 'Page deleted.');
    }

    public function trash(PageIndexRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Page::class);

        $pages = Page::onlyTrashed()
            ->with(['creator', 'updater', 'deleter'])
            ->latest('deleted_at')
            ->paginate($request->integer('per_page', 15));

        return response()->success(PageResource::collection($pages)->response()->getData(true));
    }

    public function restore(Page $page): JsonResponse
    {
        $this->authorize('restore', $page);

        $page->restore();

        return response()->success(new PageResource($page->fresh(['creator', 'updater'])), 'Page restored.');
    }
}
