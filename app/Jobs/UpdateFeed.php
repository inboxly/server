<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Feed;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inboxly\Receiver\Entry as ReceiverEntry;
use Inboxly\Receiver\Feed as ReceiverFeed;
use Inboxly\Receiver\Managers\FetcherManager;

class UpdateFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Feed $feed)
    {
    }

    /**
     * Execute the job.
     *
     * @param \Inboxly\Receiver\Managers\FetcherManager $manager
     * @return void
     */
    public function handle(FetcherManager $manager): void
    {
        $entries = $this->fetch($manager);

        if ($entries === null) {
            return;
        }

        $newEntries = $entries->filter(
            fn(Entry $entry) => $entry->wasRecentlyCreated
        );

        $this->createReadStatesForSubscribers($newEntries);
    }

    /**
     * Fetch updates for feed and return entries
     *
     * @param \Inboxly\Receiver\Managers\FetcherManager $manager
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function fetch(FetcherManager $manager): ?Collection
    {
        $receiverFeed = $manager->fetch(
            $this->feed->parameters,
            $this->feed->updated_at,
        );

        if ($receiverFeed === null) {
            return null;
        }

        $updatedFeed = Feed::fromReceiverFeed($receiverFeed);

        return $this->updateEntries($receiverFeed, $updatedFeed);
    }

    /**
     * Update entries
     *
     * @param ReceiverFeed $receiverFeed
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function updateEntries(ReceiverFeed $receiverFeed, Feed $feed): Collection
    {
        return Collection::make($receiverFeed->entries)
            ->sortBy(fn(ReceiverEntry $receiverEntry) => $receiverEntry->createdAt)
            ->map(
                fn(ReceiverEntry $receiverEntry) => Entry::fromReceiverEntry($receiverEntry, $feed),
            );
    }

    /**
     * Create entries for each user subscribed to feed
     *
     * @param \Illuminate\Database\Eloquent\Collection $entries
     */
    protected function createReadStatesForSubscribers(Collection $entries): void
    {
        $this->feed->subscribers()->each(function (User $user) use ($entries) {
            $user->entries()->syncWithPivotValues($entries, ['feed_id' => $this->feed->id], false);
        });
    }
}
