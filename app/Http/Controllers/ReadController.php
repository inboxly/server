<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class ReadController extends Controller
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

        $user->entries()
            ->whereNull('read_at')
            ->when(
                $request->has('todayOnly'),
                // todo: use date of creating an original entry instead
                fn(Builder $builder) => $builder->where('created_at', '>=', Carbon::today())
            )
            ->update(['read_at' => now()]);

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
        $this->authorize('update', $category);

        $feedsIds = $category->feeds()->get()->modelKeys();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->entries()
            ->whereIn('feed_id', $feedsIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark collection entries as read
     *
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function collection(Collection $collection): Response
    {
        $this->authorize('update', $collection);

        $collection->entries()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark feed entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function feed(Request $request, Feed $feed): Response
    {
        $this->authorize('update', $feed);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->entries()
            ->where('feed_id', $feed->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Mark all entries as read
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function saved(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->entries()
            ->whereNull('read_at')
            ->whereNotNull('saved_at')
            ->update(['read_at' => now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
