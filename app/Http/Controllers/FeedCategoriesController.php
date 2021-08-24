<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchCategoriesRequest;
use App\Models\Feed;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FeedCategoriesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\BatchCategoriesRequest $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(BatchCategoriesRequest $request, Feed $feed): Response
    {
        DB::beginTransaction();

        $feed->categories()->syncWithoutDetaching($request->ids());

        $request->user()->subscribedFeeds()->syncWithoutDetaching($feed);

        DB::commit();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Http\Requests\BatchCategoriesRequest $request
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function destroy(BatchCategoriesRequest $request, Feed $feed): Response
    {
        DB::beginTransaction();

        $feed->categories()->detach($request->ids());

        if ($feed->userCategories()->doesntExist()) {
            $request->user()->subscribedFeeds()->detach($feed);
        }

        DB::commit();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
