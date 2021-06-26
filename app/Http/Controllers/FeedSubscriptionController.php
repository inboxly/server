<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FeedSubscribeRequest;
use App\Http\Resources\FeedResource;
use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\OriginalFeed;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class FeedSubscriptionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\FeedSubscribeRequest $request
     * @param \App\Models\OriginalFeed $originalFeed
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function subscribe(FeedSubscribeRequest $request, OriginalFeed $originalFeed): JsonResource
    {
        /** @var Feed $feed */
        $feed = $request->user()->feeds()->firstOrCreate([
            'original_feed_id' => $originalFeed->id,
        ]);

        if ($feed->wasRecentlyCreated && $originalFeed->next_update_at === null) {
            $originalFeed->update([
                'next_update_at' => Carbon::now(),
            ]);
        }

        if ($request->hasCategoryIds()) {
            $feed->categories()->syncWithoutDetaching(
                $request->categoryIds()
            );
        }

        $latestOriginalEntries = $originalFeed->originalEntries()->latest('created_at')->limit(10)->get();

        $entries = $latestOriginalEntries
            ->sortBy(fn(OriginalEntry $originalEntry) => $originalEntry->created_at)
            ->map(function (OriginalEntry $originalEntry) use ($feed) {
                return ['user_id' => $feed->user_id, 'original_entry_id' => $originalEntry->id];
            })
            ->toArray();

        $feed->entries()->createMany($entries);

        $feed->load(['original', 'categories']);

        return FeedResource::make($feed);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unsubscribe(Feed $feed): Response
    {
        $this->authorize('delete', $feed);

        $feed->categories()->detach();
        $feed->entries()->delete();
        $feed->delete();

        if (Feed::where('original_feed_id', $feed->original_feed_id)->doesntExist()) {
            $feed->original->update(['next_update_at' => null]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
