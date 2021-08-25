<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EntryResource;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function __invoke(Request $request, Feed $feed): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($request->get('state') === 'unread') {
            $builder = $user->unreadEntries()->where('entries.feed_id', $feed->getKey());
        } else if ($request->get('state') === 'read') {
            $builder = $user->readEntries()->where('entries.feed_id', $feed->getKey());
        } else {
            $builder = $feed->entries();
        }

        $entries = $builder
            ->with(['userCollections', 'userReadState', 'feed.userCategories'])
            ->when(
                $request->has('oldest'),
                fn(Builder $builder) => $builder->oldest('entries.created_at'),
                fn(Builder $builder) => $builder->latest('entries.created_at')
            )
            ->cursorPaginate();

        return EntryResource::collection($entries);
    }
}
