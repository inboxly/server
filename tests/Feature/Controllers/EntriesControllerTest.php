<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\EntriesController
 */
class EntriesControllerTest extends TestCase
{
    private EloquentCollection $readEntries;
    private EloquentCollection $unreadEntries;

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_list_of_all_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_list_of_all_unread_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?state=unread");

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
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_unread_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?state=unread&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_list_of_all_read_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?state=read");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unreadEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_all_read_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?state=read&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_list_of_today_entries(): void
    {
        // Setup
        $this->prepareEntries();

        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feed);
        $oldEntries = Entry::factory(2)->create([
            'feed_id' => $feed->getKey(),
            'created_at' => fn () => Carbon::now()->subWeek(),
        ]);
        $this->user->entries()->attach($oldEntries->first()->id, [
            'feed_id' => $oldEntries->first()->feed_id,
            'read_at' => now()
        ]);
        $this->user->entries()->attach($oldEntries->last()->id, [
            'feed_id' => $oldEntries->last()->feed_id,
            'read_at' => now()
        ]);

        // Run
        $response = $this->asUser()->getJson("api/entries?todayOnly=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJsonMissing(['data' => [
            ['id' => $oldEntries->last()->id],
            ['id' => $oldEntries->first()->id],
        ]]);

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->readEntries->first()->id],
        ]]);


        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::__invoke()
     */
    public function user_can_get_reversed_list_of_today_entries(): void
    {
        // Setup
        $this->prepareEntries();

        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feed);
        $oldEntries = Entry::factory(2)->create([
            'feed_id' => $feed->getKey(),
            'created_at' => fn () => Carbon::now()->subWeek(),
        ]);
        $this->user->entries()->attach($oldEntries->first()->id, [
            'feed_id' => $oldEntries->first()->feed_id,
            'read_at' => null,
        ]);
        $this->user->entries()->attach($oldEntries->last()->id, [
            'feed_id' => $oldEntries->last()->feed_id,
            'read_at' => null,
        ]);

        // Run
        $response = $this->asUser()->getJson("api/entries?todayOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJsonMissing(['data' => [
            ['id' => $oldEntries->first()->id],
            ['id' => $oldEntries->last()->id],
        ]]);

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->id],
            ['id' => $this->readEntries->last()->id],
            ['id' => $this->unreadEntries->first()->id],
            ['id' => $this->unreadEntries->last()->id],
        ]]);


        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    protected function prepareEntries(): void
    {
        // Main models
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($feed);

        // Read Entries
        $this->readEntries = Entry::factory(2)->today()->create([
            'feed_id' => $feed->getKey(),
        ]);
        $this->user->entries()->attach($this->readEntries->first()->id, [
            'feed_id' => $this->readEntries->first()->feed_id,
            'read_at' => now()
        ]);
        $this->user->entries()->attach($this->readEntries->last()->id, [
            'feed_id' => $this->readEntries->last()->feed_id,
            'read_at' => now()
        ]);

        // Unread Entries
        $this->unreadEntries = Entry::factory(2)->today()->create([
            'feed_id' => $feed->getKey(),
        ]);
        $this->user->entries()->attach($this->unreadEntries->first()->id, [
            'feed_id' => $this->unreadEntries->first()->feed_id,
            'read_at' => null
        ]);
        $this->user->entries()->attach($this->unreadEntries->last()->id, [
            'feed_id' => $this->unreadEntries->last()->feed_id,
            'read_at' => null
        ]);

        // Category feeds
        $category = $categories->first();
        $category->feeds()->sync($feed);

        // Collection Entries
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->readEntries);
        $collection->entries()->syncWithoutDetaching($this->unreadEntries);
    }

    protected function entryStructure(): array
    {
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
