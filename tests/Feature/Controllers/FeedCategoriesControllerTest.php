<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FeedCategoriesController
 */
class FeedCategoriesControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function can_add_some_categories_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->sync($categories);
        $newCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->original_feed_id/categories", [
            'ids' => $newCategories->modelKeys()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', [...$categories->modelKeys(), ...$newCategories->modelKeys()])
            ->get();

        $this->assertCount(4, $expectedCategories);
        $this->assertDatabaseCount('category_feed', 4);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function cannot_add_categories_to_not_its_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $newCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->original_feed_id/categories", [
            'ids' => $newCategories->modelKeys()
        ]);

        // Asserts
        $response->assertNotFound();
        $this->assertDatabaseCount('category_feed', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function cannot_add_not_its_categories_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $newCategories = Category::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->original_feed_id/categories", [
            'ids' => $newCategories->modelKeys()
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('category_feed', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function can_remove_some_categories_from_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->sync($categories);
        $otherCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->sync($otherCategories);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->original_feed_id/categories", [
            'ids' => $categories->modelKeys(),
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $otherCategories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feed', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function cannot_remove_categories_from_not_its_feed(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $otherUser->getKey()]);
        $categories = Category::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $feed->categories()->sync($categories);
        $userCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->original_feed_id/categories", [
            // used "userCategories" instead "categories" for skip ids validation
            // and continue checking wrong feed
            'ids' => $userCategories->modelKeys(),
        ]);

        // Asserts
        $response->assertNotFound();

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $categories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feed', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function cannot_remove_not_its_categories_from_feed(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $categories = Category::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $feed->categories()->sync($categories);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->original_feed_id/categories", [
            'ids' => $categories->modelKeys(),
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $categories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feed', 2);
    }
}
