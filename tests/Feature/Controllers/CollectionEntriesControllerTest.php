<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use App\Models\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CollectionEntriesController
 */
class CollectionEntriesControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::index()
     */
    public function user_can_get_list_of_entries_in_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $feed = Feed::factory()->create();
        $this->user->mainCategory->feeds()->attach($feed);
        $entries = Entry::factory(2)->create(['feed_id' => $feed->id]);
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create(['feed_id' => $feed->id]);

        // Run
        $response = $this->asUser()->getJson("api/collections/$collection->id/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonStructure([
            'data' => [
                $this->entryStructure(),
            ],
        ]);

        $response->assertJson([
            'data' => [
                ['id' => $entries->last()->id],
                ['id' => $entries->first()->id],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherEntries->last()->id],
                ['id' => $otherEntries->first()->id],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::index()
     */
    public function user_can_get_reversed_list_of_entries_in_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $feed = Feed::factory()->create();
        $this->user->mainCategory->feeds()->attach($feed);
        $entries = Entry::factory(2)->create(['feed_id' => $feed->id]);
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create(['feed_id' => $feed->id]);

        // Run
        $response = $this->asUser()->getJson("api/collections/$collection->id/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonStructure([
            'data' => [
                $this->entryStructure(),
            ],
        ]);

        $response->assertJson([
            'data' => [
                ['id' => $entries->first()->id],
                ['id' => $entries->last()->id],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherEntries->first()->id],
                ['id' => $otherEntries->last()->id],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::store()
     */
    public function user_can_add_some_entries_to_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create();
        $collection->entries()->sync($entries);
        $newEntries = Entry::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/collections/$collection->id/entries", [
            'ids' => $newEntries->modelKeys()
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedEntries = $collection->entries()
            ->whereIn('entries.id', [...$entries->modelKeys(), ...$newEntries->modelKeys()])
            ->get();

        $this->assertCount(4, $expectedEntries);
        $this->assertDatabaseCount('collection_entries', 4);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::store()
     */
    public function user_cannot_add_entries_to_not_its_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create();
        $newEntries = Entry::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/collections/$collection->id/entries", [
            'ids' => $newEntries->modelKeys()
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseCount('collection_entries', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::destroy()
     */
    public function user_can_remove_some_entries_from_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create();
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create();
        $collection->entries()->sync($otherEntries);

        // Run
        $response = $this->asUser()->deleteJson("api/collections/$collection->id/entries", [
            'ids' => $entries->modelKeys(),
        ]);

        // Asserts
        $response->assertNoContent();

        $expectedEntries = $collection->entries()
            ->whereIn('entries.id', $otherEntries->modelKeys())
            ->get();

        $this->assertCount(2, $expectedEntries);
        $this->assertDatabaseCount('collection_entries', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::destroy()
     */
    public function user_cannot_remove_entries_from_not_its_collection(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $otherUser->getKey()]);
        $entries = Entry::factory(2)->create();
        $collection->entries()->sync($entries);

        // Run
        $response = $this->asUser()->deleteJson("api/collections/$collection->id/entries", [
            'ids' => $entries->modelKeys(),
        ]);

        // Asserts
        $response->assertForbidden();

        $expectedEntries = $collection->entries()
            ->whereIn('entries.id', $entries->modelKeys())
            ->get();

        $this->assertCount(2, $expectedEntries);
        $this->assertDatabaseCount('collection_entries', 2);
    }

    protected function entryStructure()
    {
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
