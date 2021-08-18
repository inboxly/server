<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\FeedCountResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedsCountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function __invoke(Request $request): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $feeds = $user->feeds()
            ->withCount([
                'entries' => fn(Builder $builder) => $builder->whereNull('read_at')
            ])
            ->get(['feeds.original_feed_id']);

        return FeedCountResource::collection($feeds);
    }
}
