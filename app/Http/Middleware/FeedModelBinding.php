<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Feed;
use Closure;
use Illuminate\Http\Request;

class FeedModelBinding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->route()->hasParameter('feedByOriginalFeedId')) {
            $this->bindFeedModel($request);
        }

        return $next($request);
    }

    /**
     * Bind route parameter to Feed model instead of OriginalFeed model
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function bindFeedModel(Request $request): void
    {
        $originalFeedId = (int)$request->route()->parameter('feedByOriginalFeedId');

        $feed = Feed::query()
            ->where('user_id', $request->user()->getKey())
            ->where('original_feed_id', $originalFeedId)
            ->firstOrFail();

        $request->route()->setParameter('feedByOriginalFeedId', $feed);
    }
}
