<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\FeedEntriesController
 */
class FeedEntriesControllerTest extends TestCase
{
    private Feed $feed;
    private EloquentCollection $readEntries;
    private EloquentCollection $unreadEntries;
    private EloquentCollection $otherReadEntries;
    private EloquentCollection $otherUnreadEntries;

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_list_of_all_entries_in_subscribed_feed(): void
    {
        // Setup
        $this->prepareEntries(subscribed: true);

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherReadEntries->first()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_list_of_all_entries_in_not_subscribed_feed(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherReadEntries->first()->id],
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_entries_in_subscribed_feed(): void
    {
        // Setup
        $this->prepareEntries(subscribed: true);

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_entries_in_not_subscribed_feed(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_list_of_all_unread_entries_in_subscribed_feed(): void
    {
        $this->prepareEntries(subscribed: true);

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?state=unread");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherReadEntries->first()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_list_of_all_unread_entries_in_not_subscribed_feed(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?state=unread");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        $response->assertJson(['data' => [
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherReadEntries->first()->id],
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_unread_entries_in_subscribed_feed(): void
    {
        $this->prepareEntries(subscribed: true);

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?state=unread&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_unread_entries_in_not_subscribed_feed(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->id}/entries?state=unread&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        $response->assertJson(['data' => [
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->id],
            ['id' => $this->otherReadEntries->last()->id],
            ['id' => $this->otherUnreadEntries->first()->id],
            ['id' => $this->otherUnreadEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
        ]]);
    }

    protected function prepareEntries(bool $subscribed = false)
    {
        // Main models
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $this->feed = Feed::factory()->create();

        // Read Entries
        $this->readEntries = Entry::factory(2)->create([
            'feed_id' => $this->feed->getKey(),
        ]);
        $this->user->entries()->attach($this->readEntries->first()->id, [
            'feed_id' => $this->readEntries->first()->feed_id,
            'read_at' => now()
        ]);
        $this->user->entries()->attach($this->readEntries->last()->id, [
            'feed_id' => $this->readEntries->last()->feed_id,
            'read_at' => now()
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->readEntries);

        if ($subscribed) {
            $this->user->subscribedFeeds()->syncWithoutDetaching($this->feed);
            $category = $categories->first();
            $category->feeds()->sync($this->feed);

            // Unread Entries
            $this->unreadEntries = Entry::factory(2)->create([
                'feed_id' => $this->feed->getKey(),
            ]);
            $this->user->entries()->attach($this->unreadEntries->first()->id, [
                'feed_id' => $this->unreadEntries->first()->feed_id,
                'read_at' => null
            ]);
            $this->user->entries()->attach($this->unreadEntries->last()->id, [
                'feed_id' => $this->unreadEntries->last()->feed_id,
                'read_at' => null
            ]);

            $collection->entries()->syncWithoutDetaching($this->unreadEntries);
        }

        // Other models
        $otherFeed = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($otherFeed);

        // Other Read Entries
        $this->otherReadEntries = Entry::factory(2)->create([
            'feed_id' => $otherFeed->getKey(),
        ]);
        $this->user->entries()->attach($this->otherReadEntries->first()->id, [
            'feed_id' => $this->otherReadEntries->first()->feed_id,
            'read_at' => now()
        ]);
        $this->user->entries()->attach($this->otherReadEntries->last()->id, [
            'feed_id' => $this->otherReadEntries->last()->feed_id,
            'read_at' => now()
        ]);

        // Other Unread Entries
        $this->otherUnreadEntries = Entry::factory(2)->create([
            'feed_id' => $otherFeed->getKey(),
        ]);
        $this->user->entries()->attach($this->otherUnreadEntries->first()->id, [
            'feed_id' => $this->otherUnreadEntries->first()->feed_id,
            'read_at' => null
        ]);
        $this->user->entries()->attach($this->otherUnreadEntries->last()->id, [
            'feed_id' => $this->otherUnreadEntries->last()->feed_id,
            'read_at' => null
        ]);
    }

    protected function entryStructure(): array
    {
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
