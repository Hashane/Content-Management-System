<?php

namespace App\Console\Commands;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishDuePages extends Command
{
    protected $signature = 'pages:publish-due';

    protected $description = 'Check drafts and publish when due.';

    public function handle(): int
    {
        $duePages = Page::where('status', PageStatus::Draft)
            ->get()
            ->filter(fn (Page $page) => is_publish_date_due($page->published_at));

        foreach ($duePages as $page) {
            $page->update(['published_at' => now(), 'status' => PageStatus::Published->value]);
            $this->info("Live: {$page->title} ({$page->slug})");
        }
        return self::SUCCESS;
    }
}
