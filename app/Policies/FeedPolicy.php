<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FeedPolicy
{
    use HandlesAuthorization;

    /**
     * The maximum number of feeds to which the user can subscribe
     */
    public const MAX_COUNT_OF_FEEDS = 1000;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feed  $feed
     * @return bool
     */
    public function view(User $user, Feed $feed): bool
    {
        return $feed->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $user->feeds_count < self::MAX_COUNT_OF_FEEDS
            ? Response::allow()
            : Response::deny('The limit on the number of feeds will be achieved.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feed  $feed
     * @return bool
     */
    public function update(User $user, Feed $feed): bool
    {
        return $feed->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feed  $feed
     * @return bool
     */
    public function delete(User $user, Feed $feed): bool
    {
        return $feed->user_id === $user->id;
    }
}
