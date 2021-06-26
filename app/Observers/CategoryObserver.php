<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "saved" event.
     *
     * @param \App\Models\Category $category
     * @return void
     */
    public function saved(Category $category): void
    {
        // Ensure that only one category marked as default
        if ($category->wasChanged('is_default') && $category->is_default === true) {
            $category->user->categories()->whereKeyNot($category->id)->update([
                'is_default' => false,
            ]);
        }
    }
}
