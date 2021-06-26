<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Feed;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FeedsController
 */
class FeedsControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\FeedsController::index()
     */
    public function can_get_list_of_all_subscribed_feeds(): void
    {
        // Setup
        $feeds = Feed::factory(2)->create(['user_id' => $this->user->getKey()]);
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feeds);
        $otherFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->getJson('api/feeds');

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
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
        $response->assertJsonStructure([
            'data' => [
                $this->feedStructure(),
            ]
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedsController::show()
     */
    public function can_get_one_feed(): void
    {
        // Setup
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feed);

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$feed->original_feed_id}");

        // Asserts
        $response->assertOk();

        $response->assertJsonStructure([
            'data' => $this->feedStructure(),
        ]);

        $response->assertJson([
            'data' => [
                'id' => $feed->original_feed_id,
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedsController::show()
     */
    public function cannot_get_not_its_feed(): void
    {
        // Setup
        $feed = Feed::factory()->create();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$feed->original_feed_id}");

        // Asserts
        $response->assertNotFound();
    }

    protected function feedStructure()
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
