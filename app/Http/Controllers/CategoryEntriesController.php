<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EntryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Category $category): ResourceCollection
    {
        $this->authorize('view', $category);

        $feedsIds = $category->feeds()->pluck('feeds.id');

        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $request->user()->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->whereIn('feed_id', $feedsIds)
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
