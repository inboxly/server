<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
}
