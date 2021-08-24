<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CollectionPolicy
{
    use HandlesAuthorization;

    /**
     * Maximum number of entry collections that the user can create
     *
     * Max count of "custom" collection +1 for predefined "saved" collection
     */
    public const MAX_COUNT_OF_COLLECTIONS = 11;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return bool
     */
    public function view(User $user, Collection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $user->collections_count < self::MAX_COUNT_OF_COLLECTIONS
            ? Response::allow()
            : Response::deny('The limit on the number of collections will be achieved.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return bool
     */
    public function update(User $user, Collection $collection): bool
    {
        return $this->isUserCustomCollection($user, $collection);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return bool
     */
    public function delete(User $user, Collection $collection): bool
    {
        return $this->isUserCustomCollection($user, $collection);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Collection $collection
     * @return bool
     */
    public function manageEntries(User $user, Collection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Collection $collection
     * @return bool
     */
    private function isUserCustomCollection(User $user, Collection $collection): bool
    {
        return $collection->user_id === $user->id && $collection->type === Collection::TYPE_CUSTOM;
    }
}
