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
    public function user_can_get_list_of_all_subscribed_feeds(): void
    {
        // Setup
        $feeds = Feed::factory(2)->create();
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feeds);
        $this->user->subscribedFeeds()->sync($feeds);

        $otherFeeds = Feed::factory(2)->create();

        // Run
        $response = $this->asUser()->getJson('api/feeds');

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
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
    public function user_can_get_one_subscribed_feed(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        /** @var Category $category */
        $category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $category->feeds()->sync($feed);
        $this->user->subscribedFeeds()->sync($feed);

        // Run
        $response = $this->asUser()->getJson("api/feeds/$feed->id");

        // Asserts
        $response->assertOk();

        $response->assertJsonStructure([
            'data' => $this->feedStructure(),
        ]);

        $response->assertJson([
            'data' => [
                'id' => $feed->id,
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedsController::show()
     */
    public function user_can_get_one_not_subscribed_feed(): void
    {
        // Setup
        $feed = Feed::factory()->create();

        // Run
        $response = $this->asUser()->getJson("api/feeds/$feed->id");

        // Asserts
        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $feed->id,
            ],
        ]);
    }

    protected function feedStructure()
    {
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
