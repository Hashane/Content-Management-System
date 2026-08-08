<?php

namespace App\Actions\Pages;

use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class SavePageAction
{
    /**
     * Create or update a page: resolves a unique slug, sanitizes the body,
     * and swaps the cover image.
     *
     * @param  array<string, mixed>  $data  Validated fields, excluding the cover image file.
     */
    public function handle(array $data, ?UploadedFile $coverImage, ?Page $page = null): Page
    {
        $data['slug'] = Page::resolveUniqueSlug($data['slug'] ?? null, $data['title'], $page?->id);
        $data['body_html'] = Purifier::clean($data['body_html']);

        $previousCoverPath = $page?->cover_image_path;

        if ($coverImage) {
            $data['cover_image_path'] = $coverImage->store('pages/covers', 'public');
        }

        if ($page) {
            $page->update($data);
        } else {
            $page = Page::create($data);
        }

        if ($coverImage && $previousCoverPath) {
            Storage::disk('public')->delete($previousCoverPath);
        }

        return $page;
    }
}
