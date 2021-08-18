<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Collection;
use App\Models\User;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CollectionsController
 */
class CollectionsControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::index()
     */
    public function can_get_list_of_collections(): void
    {
        // Setup
        $collections = Collection::factory(2)->create(['user_id' => $this->user->getKey()]);
        $alienCollections = Collection::factory(2)->create(['user_id' => User::factory()->create()->getKey()]);

        // Run
        $response = $this->asUser()->getJson('api/collections');

        // Asserts
        $response->assertOk();

        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $collections->first()->getKey()],
            ['id' => $collections->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $alienCollections->first()->getKey()],
            ['id' => $alienCollections->last()->getKey()],
        ]]);

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ]
            ],
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::store()
     */
    public function can_create_new_collection(): void
    {
        // Run
        $response = $this->asUser()->postJson('api/collections', [
            'name' => 'new_collection',
        ]);

        // Asserts
        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'name' => 'new_collection',
            ]
        ]);

        $response->assertJsonStructure(['data' => [
            'id',
            'name',
        ]]);

    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::update()
     */
    public function can_rename_one_collection(): void
    {
        // Setup
        $collection = Collection::factory()->create([
            'user_id' => $this->user->getKey(),
            'name' => 'name',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/collections/{$collection->getKey()}", [
            'name' => 'renamed',
        ]);

        // Asserts
        $response->assertNoContent();
        $this->assertDatabaseHas(Collection::newModelInstance()->getTable(), [
            'id' => $collection->getKey(),
            'name' => 'renamed',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::update()
     */
    public function cannot_rename_not_its_collection(): void
    {
        // Setup
        $collection = Collection::factory()->create([
            'name' => 'name',
        ]);

        // Run
        $response = $this->asUser()->putJson("api/collections/{$collection->getKey()}", [
            'name' => 'renamed',
        ]);

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas(Collection::newModelInstance()->getTable(), [
            'id' => $collection->getKey(),
            'name' => 'name',
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::destroy()
     */
    public function can_delete_one_collection(): void
    {
        // Setup
        $collection = Collection::factory()->create([
            'user_id' => $this->user->getKey(),
        ]);

        // Run
        $response = $this->asUser()->deleteJson("api/collections/{$collection->getKey()}");

        // Asserts
        $response->assertNoContent();
        $this->assertDeleted($collection);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CollectionsController::destroy()
     */
    public function cannot_delete_not_its_collection(): void
    {
        // Setup
        $collection = Collection::factory()->create();

        // Run
        $response = $this->asUser()->deleteJson("api/collections/{$collection->getKey()}");

        // Asserts
        $response->assertForbidden();
        $this->assertDatabaseHas($collection->getTable(), ['id' => $collection->getKey()]);
    }
}
