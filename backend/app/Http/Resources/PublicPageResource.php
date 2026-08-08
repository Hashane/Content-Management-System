<?php

namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Page
 */
class PublicPageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'body_html' => $this->body_html,
            'cover_image_url' => $this->cover_image_path
                ? Storage::disk('public')->url($this->cover_image_path)
                : null,
            'published_at' => $this->published_at,
        ];
    }
}
