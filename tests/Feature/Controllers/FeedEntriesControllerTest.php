<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

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
     * @see \App\Http\Controllers\FeedEntriesController::index()
     */
    public function can_get_list_of_all_entries_in_feed(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->original_feed_id}/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::index()
     */
    public function cannot_get_list_of_entries_in_not_its_feed(): void
    {
        // Setup
        $this->feed = Feed::factory()->create();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->original_feed_id}/entries");

        // Asserts
        $response->assertNotFound();
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::index()
     */
    public function can_get_reversed_list_of_all_entries_in_feed(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/feeds/{$this->feed->original_feed_id}/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherUnreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::index()
     */
    public function can_get_list_of_all_unread_entries_in_feed(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->original_feed_id}/entries?unreadOnly=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\FeedEntriesController::index()
     */
    public function can_get_reversed_list_of_all_unread_entries_in_feed(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/feeds/{$this->feed->original_feed_id}/entries?unreadOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    protected function prepareEntries()
    {
        // Main models
        $this->feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->readEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $this->feed->getKey(),
        ]);
        $this->unreadEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $this->feed->getKey(),
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->readEntries);
        $collection->entries()->syncWithoutDetaching($this->unreadEntries);

        // Other models
        $otherFeed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->otherReadEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $otherFeed->getKey(),
        ]);
        $this->otherUnreadEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $otherFeed->getKey(),
        ]);
    }

    protected function entryStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
