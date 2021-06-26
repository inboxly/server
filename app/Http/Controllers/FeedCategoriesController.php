<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchCategoriesRequest;
use App\Models\Feed;
use Illuminate\Http\Response;

class FeedCategoriesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\BatchCategoriesRequest $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(BatchCategoriesRequest $request, Feed $feed): Response
    {
        $this->authorize('update', $feed);

        $feed->categories()->syncWithoutDetaching(
            $request->ids()
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Http\Requests\BatchCategoriesRequest $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(BatchCategoriesRequest $request, Feed $feed): Response
    {
        $this->authorize('update', $feed);

        $feed->categories()->detach(
            $request->ids()
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
