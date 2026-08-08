<?php

use Carbon\CarbonInterface;

if (! function_exists('is_publish_date_due')) {
    /**
     * Determine whether a page's publish date has arrived.
     *
     * A null publish date means "publish immediately" (due). A past or
     * present date is due; a future date is not.
     */
    function is_publish_date_due(?CarbonInterface $publishedAt): bool
    {
        return $publishedAt === null || $publishedAt->lessThanOrEqualTo(now());
    }
}
