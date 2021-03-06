<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Maximum number of entry categories that the user can create
     *
     * Max count of "custom" category +1 for predefined "main" category
     */
    public const MAX_COUNT_OF_CATEGORIES = 11;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function view(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response
     */
    public function create(User $user): Response
    {
        return $user->categories_count < self::MAX_COUNT_OF_CATEGORIES
            ? Response::allow()
            : Response::deny('The limit on the number of categories will be achieved.');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function update(User $user, Category $category): bool
    {
        return $this->isUserCustomCategory($user, $category);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function delete(User $user, Category $category): bool
    {
        return $this->isUserCustomCategory($user, $category);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Category $category
     * @return bool
     */
    public function manageFeeds(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Category $category
     * @return bool
     */
    private function isUserCustomCategory(User $user, Category $category): bool
    {
        return $category->user_id === $user->id && $category->type === Category::TYPE_CUSTOM;
    }
}
