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
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $feed->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->when(
                $request->has('unreadOnly'),
                fn(Builder $builder) => $builder->whereNull('read_at')
            )
            ->when(
                $request->has('oldest'),
                // todo: use date of creating an original entry instead
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            );

        $entries = $builder->cursorPaginate()->withQueryString();

        return EntryResource::collection($entries);
    }
}
