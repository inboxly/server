<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Collection;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Http\Response;
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
    public function can_get_list_of_entries_in_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);

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
                ['id' => $entries->last()->getKey()],
                ['id' => $entries->first()->getKey()],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherEntries->last()->getKey()],
                ['id' => $otherEntries->first()->getKey()],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::index()
     */
    public function can_get_reversed_list_of_entries_in_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);

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
                ['id' => $entries->first()->getKey()],
                ['id' => $entries->last()->getKey()],
            ],
        ]);

        $response->assertJsonMissing([
            'data' => [
                ['id' => $otherEntries->first()->getKey()],
                ['id' => $otherEntries->last()->getKey()],
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::store()
     */
    public function can_add_some_entries_to_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->sync($entries);
        $newEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);

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
        $this->assertDatabaseCount('collection_entry', 4);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::store()
     */
    public function cannot_add_entries_to_not_its_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create();
        $newEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/collections/$collection->id/entries", [
            'ids' => $newEntries->modelKeys()
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseCount('collection_entry', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::store()
     */
    public function cannot_add_not_its_entries_to_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $newEntries = Entry::factory(2)->create();

        // Run
        $response = $this->asUser()->postJson("api/collections/$collection->id/entries", [
            'ids' => $newEntries->modelKeys()
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertDatabaseCount('collection_entry', 0);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::destroy()
     */
    public function can_remove_some_entries_from_collection(): void
    {
        // Setup
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->sync($entries);
        $otherEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);
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
        $this->assertDatabaseCount('collection_entry', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::destroy()
     */
    public function cannot_remove_entries_from_not_its_collection(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $otherUser->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $collection->entries()->sync($entries);
        $userEntries = Entry::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Run
        $response = $this->asUser()->deleteJson("api/collections/$collection->id/entries", [
            // used "userEntries" instead "entries" for skip ids validation
            // and continue checking wrong collection
            'ids' => $userEntries->modelKeys(),
        ]);

        // Asserts
        $response->assertForbidden();

        $expectedEntries = $collection->entries()
            ->whereIn('entries.id', $entries->modelKeys())
            ->get();

        $this->assertCount(2, $expectedEntries);
        $this->assertDatabaseCount('collection_entry', 2);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionEntriesController::destroy()
     */
    public function cannot_remove_not_its_entries_from_collection(): void
    {
        // Setup
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $otherUser->getKey()]);
        $entries = Entry::factory(2)->create(['user_id' => $otherUser->getKey()]);
        $collection->entries()->sync($entries);

        // Run
        $response = $this->asUser()->deleteJson("api/collections/$collection->id/entries", [
            'ids' => $entries->modelKeys(),
        ]);

        // Asserts
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $expectedEntries = $collection->entries()
            ->whereIn('entries.id', $entries->modelKeys())
            ->get();

        $this->assertCount(2, $expectedEntries);
        $this->assertDatabaseCount('collection_entry', 2);
    }

    protected function entryStructure()
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
