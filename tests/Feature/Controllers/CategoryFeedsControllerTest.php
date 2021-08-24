<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Feed;
use App\Models\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CategoryFeedsController
 */
class CategoryFeedsControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::index()
     */
    public function user_can_get_list_of_feeds_in_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($feeds);
        $otherFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->getJson("api/categories/$category->id/feeds");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonStructure([
            'data' => [
                $this->feedStructure(),
            ],
        ]);

        $response->assertJson([
            'data' => [
                ['id' => $feeds->first()->id],
                ['id' => $feeds->last()->id],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherFeeds->first()->id],
                ['id' => $otherFeeds->last()->id],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function user_can_subscribe_to_feeds_by_adding_to_category()
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feed1 = Feed::factory()->create();
        $category->feeds()->syncWithoutDetaching($feed1);
        $feed2 = Feed::factory()->create();

        // Run
        $response = $this->asUser()->postJson("api/categories/$category->id/feeds", [
            'ids' => [$feed2->id]
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseCount('category_feeds', 2);
        $this->assertDatabaseMissing('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $feed1->id]);
        $this->assertDatabaseHas('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $feed2->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function user_can_add_some_feeds_to_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($feeds);
        $newFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/categories/$category->id/feeds", [
            'ids' => $newFeeds->pluck('id')->toArray()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', [...$feeds->modelKeys(), ...$newFeeds->modelKeys()])
            ->get();

        $this->assertCount(4, $expectedFeeds);
        $this->assertDatabaseCount('category_feeds', 4);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function user_cannot_add_feeds_to_not_its_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create();
        $newFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/categories/$category->id/feeds", [
            'ids' => $newFeeds->pluck('id')->toArray()
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseCount('category_feeds', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function user_can_remove_some_feeds_from_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($feeds);
        $otherFeeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($otherFeeds);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/$category->id/feeds", [
            'ids' => $feeds->pluck('id')->toArray()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', $otherFeeds->modelKeys())
            ->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseCount('category_feeds', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function user_can_unsubscribe_from_feeds_by_removing_from_category()
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($feeds);
        $this->user->subscribedFeeds()->syncWithoutDetaching($feeds);
        $otherFeeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($otherFeeds);
        $this->user->subscribedFeeds()->syncWithoutDetaching($otherFeeds);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/$category->id/feeds", [
            'ids' => [$feeds->first()->id]
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', $otherFeeds->modelKeys())
            ->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseMissing('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $feeds->first()->id]);
        $this->assertDatabaseHas('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $feeds->last()->id]);
        $this->assertDatabaseHas('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $otherFeeds->first()->id]);
        $this->assertDatabaseHas('subscriptions', ['user_id' => $this->user->id, 'feed_id' => $otherFeeds->last()->id]);
        $this->assertDatabaseCount('subscriptions', 3);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function user_cannot_remove_feeds_from_not_its_category(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $otherUser->getKey()]);
        $feeds = Feed::factory(2)->create();
        $category->feeds()->syncWithoutDetaching($feeds);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/$category->id/feeds", [
            'ids' => $feeds->pluck('id')->toArray()
        ]);

        // Asserts
        $response->assertForbidden();

        $expectedFeeds = $category->feeds()->whereIn('feeds.id', $feeds->modelKeys())->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseCount('category_feeds', 2);
    }

    protected function feedStructure()
    {
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
