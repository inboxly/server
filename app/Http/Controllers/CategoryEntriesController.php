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
    public function __invoke(Request $request, Category $category): ResourceCollection
    {
        $this->authorize('view', $category);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($request->get('state') === 'unread') {
            $builder = $user->unreadEntries();
        } else if ($request->get('state') === 'read') {
            $builder = $user->readEntries();
        } else {
            $builder = $user->entries();
        }

        $feedsIds = $category->feeds()->pluck('feeds.id');

        $entries = $builder
            ->whereIn('entries.feed_id', $feedsIds)
            ->with(['userCollections', 'userReadState', 'feed.userCategories'])
            ->when(
                $request->has('oldest'),
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            )
            ->cursorPaginate();

        return EntryResource::collection($entries)->preserveQuery();
    }
}
