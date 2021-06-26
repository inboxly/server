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
 * @see \App\Http\Controllers\SavedEntriesController
 */
class SavedEntriesControllerTest extends TestCase
{
    private EloquentCollection $savedEntries;
    private EloquentCollection $unsavedEntries;
    private EloquentCollection $otherSavedEntries;

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::index()
     */
    public function can_get_list_of_all_saved_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/saved/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->savedEntries->last()->getKey()],
            ['id' => $this->savedEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unsavedEntries->last()->getKey()],
            ['id' => $this->unsavedEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherSavedEntries->first()->getKey()],
            ['id' => $this->otherSavedEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::index()
     */
    public function can_get_reversed_list_of_all_saved_entries(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/saved/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->savedEntries->first()->getKey()],
            ['id' => $this->savedEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->unsavedEntries->first()->getKey()],
            ['id' => $this->unsavedEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherSavedEntries->first()->getKey()],
            ['id' => $this->otherSavedEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::store()
     */
    public function can_add_some_entries_to_saved(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->postJson("api/saved/entries", [
            'ids' => $this->unsavedEntries->modelKeys()
        ]);

        // Asserts
        $response->assertNoContent();

        $savedEntries = $this->user->entries()->whereNotNull('saved_at')->get();
        $this->assertCount(4, $savedEntries);
    }

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::store()
     */
    public function cannot_add_not_its_entries_to_saved(): void
    {
        // Setup
        /** @var Entry $entry */
        $entry = Entry::factory()->create();

        // Run
        $response = $this->asUser()->postJson("api/saved/entries", [
            'ids' => [$entry->getKey()]
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNull($entry->refresh()->saved_at);
    }

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::destroy()
     */
    public function can_remove_some_entries_from_saved(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->deleteJson("api/saved/entries", [
            'ids' => $this->savedEntries->modelKeys(),
        ]);

        // Asserts
        $response->assertNoContent();

        $savedEntries = $this->user->entries()->whereNotNull('saved_at')->get();
        $this->assertCount(0, $savedEntries);
    }

    /**
     * @test
     * @see \App\Http\Controllers\SavedEntriesController::destroy()
     */
    public function cannot_remove_not_its_entries_from_saved(): void
    {
        // Setup
        /** @var Entry $entry */
        $entry = Entry::factory()->saved()->create();

        // Run
        $response = $this->asUser()->deleteJson("api/saved/entries", [
            'ids' => [$entry->getKey()]
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertNotNull($entry->refresh()->saved_at);
    }

    protected function prepareEntries()
    {
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->savedEntries = Entry::factory(2)->saved()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);
        $this->unsavedEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);
        $this->otherSavedEntries = Entry::factory(2)->saved()->create();

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->savedEntries);
        $collection->entries()->syncWithoutDetaching($this->unsavedEntries);
    }

    protected function entryStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
