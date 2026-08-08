<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageIndexRequest;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

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

    public function store(StorePageRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['cover_image']);
        $data['slug'] = Page::resolveUniqueSlug($data['slug'] ?? null, $data['title']);
        $data['body_html'] = Purifier::clean($data['body_html']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('pages/covers', 'public');
        }

        $page = Page::create($data);

        return response()->success(new PageResource($page->load(['creator', 'updater'])), 'Page created.', 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return response()->success(new PageResource($page->load(['creator', 'updater'])));
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $data = $request->validated();
        unset($data['cover_image']);
        $data['slug'] = Page::resolveUniqueSlug($data['slug'] ?? null, $data['title'], $page->id);
        $data['body_html'] = Purifier::clean($data['body_html']);

        $previousCoverPath = $page->cover_image_path;

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('pages/covers', 'public');
        }

        $page->update($data);

        // Only remove the old file once updated
        if ($request->hasFile('cover_image') && $previousCoverPath) {
            Storage::disk('public')->delete($previousCoverPath);
        }

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
