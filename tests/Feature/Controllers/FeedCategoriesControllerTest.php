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
    public function user_can_add_own_custom_categories_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->syncWithoutDetaching($categories);
        $newCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->id/categories", [
            'ids' => $newCategories->modelKeys()
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertCount(4, $feed->categories()->get());
        $this->assertDatabaseCount('category_feeds', 4);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function user_can_add_own_main_category_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->syncWithoutDetaching($categories);

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->id/categories", [
            'ids' => [$this->user->mainCategory->id]
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertCount(3, $feed->categories()->get());
        $this->assertDatabaseCount('category_feeds', 3);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function user_cannot_add_not_own_custom_categories_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $newCategories = Category::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->id/categories", [
            'ids' => $newCategories->modelKeys()
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('category_feeds', 0);
        $this->assertDatabaseMissing('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::store()
     */
    public function user_cannot_add_not_own_main_category_to_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $user = User::factory()->create();

        // Run
        $response = $this->asUser()->postJson("api/feeds/$feed->id/categories", [
            'ids' => [$user->mainCategory->id]
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('category_feeds', 0);
        $this->assertDatabaseMissing('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function user_can_remove_own_custom_categories_from_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->syncWithoutDetaching($categories);
        $otherCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->syncWithoutDetaching($otherCategories);
        $this->user->subscribedFeeds()->syncWithoutDetaching($feed);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->id/categories", [
            'ids' => $categories->modelKeys(),
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $otherCategories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feeds', 2);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function user_can_remove_own_main_category_from_feed(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $feed->categories()->syncWithoutDetaching($this->user->mainCategory->id);
        $otherCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->syncWithoutDetaching($otherCategories);
        $this->user->subscribedFeeds()->syncWithoutDetaching($feed);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->id/categories", [
            'ids' => [$this->user->mainCategory->id],
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $otherCategories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feeds', 2);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $this->user->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function user_cannot_remove_not_own_custom_categories_from_feed(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $feed->categories()->syncWithoutDetaching($categories);
        $otherUser->subscribedFeeds()->syncWithoutDetaching($feed);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->id/categories", [
            'ids' => $categories->modelKeys(),
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $categories->modelKeys())
            ->get();

        $this->assertCount(2, $expectedCategories);
        $this->assertDatabaseCount('category_feeds', 2);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $otherUser->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedCategoriesController::destroy()
     */
    public function user_cannot_remove_not_own_main_category_from_feed(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $feed->categories()->syncWithoutDetaching([...$categories->modelKeys(), $otherUser->mainCategory->id]);
        $otherUser->subscribedFeeds()->syncWithoutDetaching($feed);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$feed->id/categories", [
            'ids' => [$otherUser->mainCategory->id],
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedCategories = $feed->categories()
            ->whereIn('categories.id', $categories->modelKeys())
            ->get();

        $this->assertCount(3, [...$expectedCategories, $otherUser->mainCategory]);
        $this->assertDatabaseCount('category_feeds', 3);
        $this->assertDatabaseHas('subscriptions', ['feed_id' => $feed->id, 'user_id' => $otherUser->id]);
    }
}
