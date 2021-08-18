<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FeedModelBinding
{
    private const ORIGINAL_PARAMETER = 'feedByOriginalFeedId';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->route()->hasParameter(self::ORIGINAL_PARAMETER)) {
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
        $originalFeedId = (int)$request->route()->parameter(self::ORIGINAL_PARAMETER);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $feed = $user->feeds()->where('original_feed_id', $originalFeedId)->firstOrFail();

        $request->route()->forgetParameter(self::ORIGINAL_PARAMETER);
        $request->route()->setParameter('feed', $feed);
    }
}
