<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EntryResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;

class EntriesController extends Controller
{
    public function __invoke(Request $request): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($request->get('state') === 'unread') {
            $builder = $user->unreadEntries();
        } else if ($request->get('state') === 'read') {
            $builder = $user->readEntries();
        } else {
            $builder = $user->entries();
        }


        $subscribedFeedIds = $user->subscribedFeeds()->pluck('feeds.id')->toArray();

        $entries = $builder
            ->whereIn('entries.feed_id', $subscribedFeedIds)
            ->with(['userCollections', 'userReadState', 'feed.userCategories'])
            ->when(
                $request->has('oldest'),
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            )
            ->when(
                $request->has('todayOnly'),
                fn(Builder $builder) => $builder->where('entries.created_at', '>=', Carbon::today())
            )
            ->cursorPaginate();

        return EntryResource::collection($entries)->preserveQuery();
    }
}
