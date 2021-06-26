<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\OriginalEntry;
use App\Models\OriginalFeed;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FeedSubscriptionController
 */
class FeedSubscriptionControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\FeedSubscriptionController::subscribe()
     */
    public function can_subscribe_to_feed(): void
    {
        // Setup
        /** @var OriginalFeed $originalFeed */
        $originalFeed = OriginalFeed::factory()->create();

        // Run
        $response = $this->asUser()->putJson("api/feeds/$originalFeed->id/subscription");

        // Asserts
        $response->assertCreated();

        $structure = $this->feedStructure();
        $structure['categories'] = [];
        $response->assertJsonStructure([
            'data' => $structure,
        ]);

        $response->assertJson([
            'data' => [
                'id' => $originalFeed->id,
                'categories' => []
            ],
        ]);

        $this->assertNotNull($originalFeed->fresh()->next_update_at);

        $this->assertDatabaseCount('category_feed', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedSubscriptionController::subscribe()
     */
    public function can_subscribe_to_feed_and_add_to_some_categories(): void
    {
        // Setup
        /** @var OriginalFeed $originalFeed */
        $originalFeed = OriginalFeed::factory()->create();
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $otherCategories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->putJson("api/feeds/$originalFeed->id/subscription", [
            'ids' => $categories->modelKeys()
        ]);

        // Asserts
        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => $this->feedStructure(),
        ]);

        $response->assertJson([
            'data' => [
                'id' => $originalFeed->id,
                'categories' => [
                    ['id' => $categories->first()->getKey()],
                    ['id' => $categories->last()->getKey()],
                ]
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                'categories' => [
                    ['id' => $otherCategories->first()->getKey()],
                    ['id' => $otherCategories->last()->getKey()],
                ]
            ],
        ]);

        $this->assertNotNull($originalFeed->fresh()->next_update_at);

        $this->assertDatabaseCount('category_feed', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedSubscriptionController::unsubscribe()
     */
    public function can_unsubscribe_from_feed(): void
    {
        // Setup
        /** @var OriginalFeed $originalFeed */
        $originalFeed = OriginalFeed::factory()->create(['next_update_at' => now()]);
        /** @var Feed $feed */
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey(), 'original_feed_id' => $originalFeed->id]);
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed->categories()->sync($categories->modelKeys());
        $originalEntry = OriginalEntry::factory()->create(['original_feed_id' => $originalFeed->id]);
        Entry::factory()->create(['feed_id' => $feed->getKey(), 'original_entry_id' => $originalEntry->getKey()]);

        // Run
        $response = $this->asUser()->deleteJson("api/feeds/$originalFeed->id/subscription");

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseCount('feeds', 0);
        $this->assertDatabaseCount('entries', 0);
        $this->assertDatabaseCount('category_feed', 0);
        $this->assertDatabaseCount('original_feeds', 1);
        $this->assertDatabaseCount('original_entries', 1);

        $this->assertNull($originalFeed->fresh()->next_update_at);
    }

    protected function feedStructure()
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
