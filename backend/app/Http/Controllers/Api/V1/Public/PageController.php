<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicPageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function __invoke(string $slug): JsonResponse
    {
        $page = Page::publishedAndDue()->where('slug', $slug)->firstOrFail();

        return response()->success(new PublicPageResource($page));
    }
}
