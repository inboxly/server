<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

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
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_list_of_all_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_reversed_list_of_all_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_list_of_all_unread_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?unreadOnly=1");

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
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_reversed_list_of_all_unread_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?unreadOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_list_of_all_read_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?readOnly=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_reversed_list_of_all_read_entries(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/entries?readOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_list_of_today_entries(): void
    {
        // Setup
        $this->prepareEntries();
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $oldEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
            'created_at' => fn () => Carbon::now()->subWeek(),
        ]);

        // Run
        $response = $this->asUser()->getJson("api/entries?todayOnly=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJsonMissing(['data' => [
            ['id' => $oldEntries->last()->getKey()],
            ['id' => $oldEntries->first()->getKey()],
        ]]);

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
        ]]);


        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\EntriesController::index()
     */
    public function can_get_reversed_list_of_today_entries(): void
    {
        // Setup
        $this->prepareEntries();
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $oldEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
            'created_at' => fn () => Carbon::now()->subWeek(),
        ]);

        // Run
        $response = $this->asUser()->getJson("api/entries?todayOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJsonMissing(['data' => [
            ['id' => $oldEntries->first()->getKey()],
            ['id' => $oldEntries->last()->getKey()],
        ]]);

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);


        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    protected function prepareEntries()
    {
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->readEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);
        $this->unreadEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->readEntries);
        $collection->entries()->syncWithoutDetaching($this->unreadEntries);
    }

    protected function entryStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
