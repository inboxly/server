<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\Response;
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
    public function can_get_list_of_feeds_in_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feeds);
        $otherFeeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->getJson("api/categories/{$category->id}/feeds");

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
                ['id' => $feeds->first()->original_feed_id],
                ['id' => $feeds->last()->original_feed_id],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherFeeds->first()->original_feed_id],
                ['id' => $otherFeeds->last()->original_feed_id],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function can_add_some_feeds_to_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feeds);
        $newFeeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/categories/{$category->id}/feeds", [
            'ids' => $newFeeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', [...$feeds->modelKeys(), ...$newFeeds->modelKeys()])
            ->get();

        $this->assertCount(4, $expectedFeeds);
        $this->assertDatabaseCount('category_feed', 4);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function cannot_add_feeds_to_not_its_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create();
        $newFeeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/categories/{$category->id}/feeds", [
            'ids' => $newFeeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseCount('category_feed', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::store()
     */
    public function cannot_add_not_its_feeds_to_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $newFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/categories/{$category->id}/feeds", [
            'ids' => $newFeeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('category_feed', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function can_remove_some_feeds_from_category(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $feeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feeds);
        $otherFeeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($otherFeeds);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->id}/feeds", [
            'ids' => $feeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', $otherFeeds->modelKeys())
            ->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseCount('category_feed', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function cannot_remove_feeds_from_not_its_category(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $otherUser->getKey()]);
        $feeds = Feed::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $category->feeds()->sync($feeds);
        $userFeeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->id}/feeds", [
            // used "$userFeeds" instead "$feeds" for skip ids validation
            // and continue checking wrong category
            'ids' => $userFeeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertForbidden();

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', $feeds->modelKeys())
            ->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseCount('category_feed', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryFeedsController::destroy()
     */
    public function cannot_remove_not_its_feeds_from_category(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $otherUser->getKey()]);
        $feeds = Feed::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $category->feeds()->sync($feeds);

        // Run
        $response = $this->asUser()->deleteJson("api/categories/{$category->id}/feeds", [
            'ids' => $feeds->pluck('original_feed_id')->toArray()
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedFeeds = $category->feeds()
            ->whereIn('feeds.id', $feeds->modelKeys())
            ->get();

        $this->assertCount(2, $expectedFeeds);
        $this->assertDatabaseCount('category_feed', 2);
    }

    protected function feedStructure()
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
