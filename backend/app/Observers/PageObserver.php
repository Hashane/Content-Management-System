<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\Auth;

class PageObserver
{
    public function creating(Page $page): void
    {
        $page->created_by = Auth::id();
        $page->updated_by = Auth::id();
    }

    public function updating(Page $page): void
    {
        $page->updated_by = Auth::id();
    }

    public function deleting(Page $page): void
    {
        if ($page->isForceDeleting()) {
            return;
        }

        $page->deleted_by = Auth::id();
        $page->saveQuietly();
    }

    public function restoring(Page $page): void
    {
        $page->deleted_by = null;
    }
}
