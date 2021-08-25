<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user): void
    {
        // Create "read later" collection
        $user->collections()->create(['type' => Collection::TYPE_READ_LATER, 'name' => 'Read Later']);

        // Create "main" category
        $user->categories()->create(['type' => Category::TYPE_MAIN, 'name' => 'Main']);
    }
}
