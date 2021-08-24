<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\FeedResource;
use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $feeds = $user->subscribedFeeds()->with(['userCategories'])->get();

        return FeedResource::collection($feeds);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Feed $feed
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(Feed $feed): JsonResource
    {
        $feed->load(['userCategories']);

        return FeedResource::make($feed);
    }
}
