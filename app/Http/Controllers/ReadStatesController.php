<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchEntriesRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\ReadState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ReadStatesController extends Controller
{
    /**
     * Mark all entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $feedIds = $user->subscribedFeeds()->pluck('feeds.id');
        $user->readStates()
            ->whereNull('read_at')
            ->whereIn('feed_id', $feedIds)
            ->when($request->has('todayOnly'), fn(Builder|ReadState $builder) => $builder->today())
            ->update(['read_at' => Carbon::now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark category entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function category(Request $request, Category $category): Response
    {
        $this->authorize('manageFeeds', $category);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $feedIds = $category->feeds()->pluck('feeds.id');

        $user->readStates()
            ->whereNull('read_at')
            ->whereIn('feed_id', $feedIds)
            ->when($request->has('todayOnly'), fn(Builder|ReadState $builder) => $builder->today())
            ->update(['read_at' => Carbon::now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark collection entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function collection(Request $request, Collection $collection): Response
    {
        $this->authorize('manageEntries', $collection);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->readStates()
            ->whereNull('read_at')
            ->whereIn('entry_id', $collection->entries()->pluck('entries.id'))
            ->when($request->has('todayOnly'), fn(Builder|ReadState $builder) => $builder->today())
            ->update(['read_at' => Carbon::now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark feed entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     */
    public function feed(Request $request, Feed $feed): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->readStates()
            ->whereNull('read_at')
            ->where('feed_id', $feed->id)
            ->when($request->has('todayOnly'), fn(Builder|ReadState $builder) => $builder->today())
            ->update(['read_at' => Carbon::now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function entries(BatchEntriesRequest $request): Response
    {
        // Skip the entries that are already marked as read
        $readEntryIds = $request->user()->readStates()
            ->whereNotNull('read_at')
            ->whereIn('entry_id', $request->ids())
            ->pluck('entry_id');
        $unreadEntryIds = collect($request->ids())->diff($readEntryIds);
        $unreadEntries = Entry::whereIn('id', $unreadEntryIds)->get(['id', 'feed_id']);

        // Mark as read rest of the entries
        $request->user()->readStates()->upsert(
            $unreadEntries->map(fn(Entry $entry) => [
                'user_id' => $request->user()->id,
                'entry_id' => $entry->id,
                'feed_id' => $entry->feed_id,
                'read_at' => Carbon::now(),
            ])->toArray(),
            ['user_id', 'entry_id', 'feed_id'],
            ['read_at']
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(BatchEntriesRequest $request): Response
    {
        $request->user()->readStates()
            ->whereNotNull('read_at')
            ->whereIn('entry_id', $request->ids())
            ->update(['read_at' => null]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
