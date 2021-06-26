<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Entry;
use App\Models\Feed;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FeedsCountsController
 */
class FeedsCountsControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\FeedsCountsController::index()
     */
    public function can_get_list_of_unread_counts_for_all_feeds(): void
    {
        // Setup
        /** @var Feed $feedWith0 */
        $feedWith0 = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feedWith0->getKey(),
        ]);

        /** @var Feed $feedWith2 */
        $feedWith2 = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feedWith2->getKey(),
        ]);
        Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feedWith2->getKey(),
        ]);

        /** @var Feed $feedWith4 */
        $feedWith4 = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feedWith4->getKey(),
        ]);
        Entry::factory(4)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feedWith4->getKey(),
        ]);

        /** @var Feed $otherFeedWith2 */
        $otherFeedWith2 = Feed::factory()->create();
        Entry::factory(2)->read()->create([
            'user_id' => $otherFeedWith2->user_id,
            'feed_id' => $otherFeedWith2->getKey(),
        ]);
        Entry::factory(2)->create([
            'user_id' => $otherFeedWith2->user_id,
            'feed_id' => $otherFeedWith2->getKey(),
        ]);

        // Run
        $response = $this->asUser()->getJson('api/feeds/counts');

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJson([
            'data' => [
                ['id' => $feedWith0->original_feed_id, 'entries_count' => 0],
                ['id' => $feedWith2->original_feed_id, 'entries_count' => 2],
                ['id' => $feedWith4->original_feed_id, 'entries_count' => 4],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherFeedWith2->original_feed_id],
            ]
        ]);
    }
}
