<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Feed;

class FeedObserver
{
    /**
     * Handle the Feed "created" event.
     *
     * @param \App\Models\Feed $feed
     * @return void
     */
    public function created(Feed $feed): void
    {
        // Schedule next update for original feed
        if ($feed->original->next_update_at === null) {
            $feed->original->update(['next_update_at' => now()]);
        }
    }
}
