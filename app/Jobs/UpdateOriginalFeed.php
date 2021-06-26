<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\OriginalFeed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Inboxly\Receiver\Entry as ReceiverEntry;
use Inboxly\Receiver\Feed as ReceiverFeed;
use Inboxly\Receiver\Managers\FetcherManager;

class UpdateOriginalFeed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\OriginalFeed
     */
    private OriginalFeed $originalFeed;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OriginalFeed $originalFeed)
    {
        $this->originalFeed = $originalFeed;
    }

    /**
     * Execute the job.
     *
     * @param \Inboxly\Receiver\Managers\FetcherManager $manager
     * @return void
     */
    public function handle(FetcherManager $manager): void
    {
        $originalEntries = $this->fetch($manager);

        if ($originalEntries === null) {
            return;
        }

        $this->createEntriesForSubscribers($originalEntries);
    }

    /**
     * Fetch updates for feed and return original entries
     *
     * @param \Inboxly\Receiver\Managers\FetcherManager $manager
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    protected function fetch(FetcherManager $manager): ?Collection
    {
        $receiverFeed = $manager->fetch(
            $this->originalFeed->parameters,
            $this->originalFeed->updated_at,
        );

        if ($receiverFeed === null) {
            return null;
        }

        $updatedOriginalFeed = OriginalFeed::fromReceiverFeed($receiverFeed);

        return $this->updateOriginalEntries($receiverFeed, $updatedOriginalFeed);
    }

    /**
     * Update original entries
     *
     * @param ReceiverFeed $receiverFeed
     * @param \App\Models\OriginalFeed $originalFeed
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function updateOriginalEntries(ReceiverFeed $receiverFeed, OriginalFeed $originalFeed): Collection
    {
        return Collection::make($receiverFeed->entries)
            ->sortBy(fn(ReceiverEntry $receiverEntry) => $receiverEntry->createdAt)
            ->map(
                fn(ReceiverEntry $receiverEntry) => OriginalEntry::fromReceiverEntry($receiverEntry, $originalFeed),
            );
    }

    /**
     * Create entries for each user subscribed to original feed
     *
     * @param \Illuminate\Database\Eloquent\Collection $originalEntries
     */
    protected function createEntriesForSubscribers(Collection $originalEntries): void
    {
        $newOriginalEntries = $originalEntries->filter(
            fn(OriginalEntry $originalEntry) => $originalEntry->wasRecentlyCreated
        );

        Feed::query()
            ->where('original_feed_id', $this->originalFeed->getKey())
            ->each(function (Feed $feed) use ($newOriginalEntries) {
                $entries = $newOriginalEntries
                    ->map(function (OriginalEntry $originalEntry) use ($feed) {
                        return ['user_id' => $feed->user_id, 'original_entry_id' => $originalEntry->id];
                    })
                    ->toArray();

                $feed->entries()->createMany($entries);
            });
    }
}
