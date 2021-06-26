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
    public function index(Request $request, Feed $feed): ResourceCollection
    {
        $builder = $feed->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->when(
                $request->has('unreadOnly'),
                fn(Builder $builder) => $builder->whereNull('read_at')
            );

        $builder = $request->has('oldest') ? $builder->oldest() : $builder->latest();

        /** @noinspection PhpUndefinedMethodInspection */
        $entries = $builder->cursorPaginate()->withQueryString();

        return EntryResource::collection($entries);
    }
}
