<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExploreRequest;
use App\Http\Resources\FeedResource;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inboxly\Receiver\Contracts\Parameters;
use Inboxly\Receiver\Entry as ReceiverEntry;
use Inboxly\Receiver\Feed as ReceiverFeed;
use Inboxly\Receiver\Managers\ExplorerManager;
use Inboxly\Receiver\Managers\FetcherManager;

class ExploreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\ExploreRequest $request
     * @param \Inboxly\Receiver\Managers\ExplorerManager $explorerManager
     * @param \Inboxly\Receiver\Managers\FetcherManager $fetcherManager
     * @param string|null $explorerKey
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function __invoke(
        ExploreRequest $request,
        ExplorerManager $explorerManager,
        FetcherManager $fetcherManager,
        string $explorerKey = null
    ): ResourceCollection
    {
        $results = $explorerManager->explore($request->exploreQuery(), $explorerKey);

        $feeds = Collection::make($results)
            ->map(fn(Parameters $parameters) => $fetcherManager->fetch($parameters))
            ->filter()
            ->map(function (ReceiverFeed $receiverFeed) {
                $feed = Feed::fromReceiverFeed($receiverFeed);

                if ($feed->wasRecentlyCreated) {
                    Collection::make($receiverFeed->entries)
                        ->sortBy(fn(ReceiverEntry $receiverEntry) => $receiverEntry->createdAt)
                        ->each(function (ReceiverEntry $receiverEntry) use ($feed) {
                            Entry::fromReceiverEntry($receiverEntry, $feed);
                        });
                }

                return $feed->load(['userCategories']);
            });

        return FeedResource::collection($feeds);
    }
}
