<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ReadEntriesController
 */
class ReadEntriesControllerTest extends TestCase
{
    private EloquentCollection $readEntries;
    private EloquentCollection $unreadEntries;
    private EloquentCollection $otherReadEntries;

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::index()
     */
    public function can_get_list_of_all_read_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/read/entries");

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

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::index()
     */
    public function can_get_reversed_list_of_all_read_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/read/entries?oldest=1");

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

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::store()
     */
    public function can_add_some_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->postJson("api/read/entries", [
            'ids' => $this->unreadEntries->modelKeys()
        ]);

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertCount(4, $readEntries);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::store()
     */
    public function cannot_add_not_its_entries_to_read(): void
    {
        // Setup
        /** @var Entry $entry */
        $entry = Entry::factory()->create();

        // Run
        $response = $this->asUser()->postJson("api/read/entries", [
            'ids' => [$entry->getKey()]
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNull($entry->refresh()->read_at);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::destroy()
     */
    public function can_remove_some_entries_from_read(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->deleteJson("api/read/entries", [
            'ids' => $this->readEntries->modelKeys(),
        ]);

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertCount(0, $readEntries);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadEntriesController::destroy()
     */
    public function cannot_remove_not_its_entries_from_read(): void
    {
        // Setup
        /** @var Entry $entry */
        $entry = Entry::factory()->read()->create();

        // Run
        $response = $this->asUser()->deleteJson("api/read/entries", [
            'ids' => [$entry->getKey()]
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotNull($entry->refresh()->read_at);
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
        $this->otherReadEntries = Entry::factory(2)->read()->create();

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
