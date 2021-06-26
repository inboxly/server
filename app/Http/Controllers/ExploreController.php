<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExploreRequest;
use App\Http\Resources\OriginalFeedResource;
use App\Models\OriginalEntry;
use App\Models\OriginalFeed;
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
    public function explore(
        ExploreRequest $request,
        ExplorerManager $explorerManager,
        FetcherManager $fetcherManager,
        string $explorerKey = null
    ): ResourceCollection
    {
        $results = $explorerManager->explore($request->exploreQuery(), $explorerKey);

        $originalFeeds = Collection::make($results)
            ->map(fn(Parameters $parameters) => $fetcherManager->fetch($parameters))
            ->filter()
            ->map(function (ReceiverFeed $receiverFeed) {
                $originalFeed = OriginalFeed::fromReceiverFeed($receiverFeed);
                $originalEntries = Collection::make($receiverFeed->entries)
                    ->sortBy(fn(ReceiverEntry $receiverEntry) => $receiverEntry->createdAt)
                    ->map(function (ReceiverEntry $receiverEntry) use ($originalFeed) {
                        return OriginalEntry::fromReceiverEntry($receiverEntry, $originalFeed)
                            ->setRelation('originalFeed', $originalFeed);
                    });
                $originalFeed->setRelation('originalEntries', $originalEntries);

                return $originalFeed;
            });

        return OriginalFeedResource::collection($originalFeeds);
    }
}
