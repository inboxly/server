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
     * @see \App\Http\Controllers\FeedsCountsController::__invoke()
     */
    public function user_can_get_list_of_unread_counts_for_all_feeds(): void
    {
        // Setup
        /** @var Feed $feedWith0 */
        $feedWith0 = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feedWith0);
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $feedWith0->getKey()]),
            ['feed_id' => $feedWith0->getKey(), 'read_at' => now()]
        );

        /** @var Feed $feedWith2 */
        $feedWith2 = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feedWith2);
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $feedWith2->getKey()]),
            ['feed_id' => $feedWith2->getKey(), 'read_at' => now()]
        );
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $feedWith2->getKey()]),
            ['feed_id' => $feedWith2->getKey()]
        );

        /** @var Feed $feedWith4 */
        $feedWith4 = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feedWith4);
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $feedWith4->getKey()]),
            ['feed_id' => $feedWith4->getKey(), 'read_at' => now()]
        );
        $this->user->entries()->attach(
            Entry::factory(4)->create(['feed_id' => $feedWith4->getKey()]),
            ['feed_id' => $feedWith4->getKey()]
        );

        /** @var Feed $otherFeedWith2 */
        $otherFeedWith2 = Feed::factory()->create();
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $otherFeedWith2->getKey()]),
            ['feed_id' => $otherFeedWith2->getKey(), 'read_at' => now()]
        );
        $this->user->entries()->attach(
            Entry::factory(2)->create(['feed_id' => $otherFeedWith2->getKey()]),
            ['feed_id' => $otherFeedWith2->getKey()]
        );

        // Run
        $response = $this->asUser()->getJson('api/feeds/counts');

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJson([
            'data' => [
                ['id' => $feedWith0->id, 'entries_count' => 0],
                ['id' => $feedWith2->id, 'entries_count' => 2],
                ['id' => $feedWith4->id, 'entries_count' => 4],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherFeedWith2->id],
            ]
        ]);
    }
}
