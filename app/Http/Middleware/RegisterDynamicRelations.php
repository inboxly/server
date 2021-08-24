<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\ReadState;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class RegisterDynamicRelations
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user() instanceof User) {
            $this->entryUserCollections($request->user());
            $this->entryUserReadState($request->user());
            $this->feedUserCategories($request->user());
        }

        return $next($request);
    }

    /**
     * @param \App\Models\User $user
     * @return void
     */
    private function entryUserCollections(User $user): void
    {
        Entry::resolveRelationUsing('userCollections', function (Entry $entry) use ($user) {
            return $entry
                ->belongsToMany(Collection::class, 'collection_entries')
                ->where('user_id', $user->getKey());
        });
    }

    /**
     * @param \App\Models\User $user
     * @return void
     */
    private function entryUserReadState(User $user): void
    {
        Entry::resolveRelationUsing('userReadState', function (Entry $entry) use ($user) {
            return $entry
                ->hasOne(ReadState::class)
                ->where('user_id', $user->getKey());
        });
    }

    /**
     * @param \App\Models\User $user
     * @return void
     */
    private function feedUserCategories(User $user): void
    {
        Feed::resolveRelationUsing('userCategories', function (Feed $feed) use ($user) {
            return $feed
                ->belongsToMany(Category::class, 'category_feeds')
                ->where('user_id', $user->getKey());
        });
    }
}
