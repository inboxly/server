<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ProfileController
 */
class ProfileControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\ProfileController::__invoke()
     */
    public function user_can_explore_feeds_from_reddit(): void
    {
        // Run
        $response = $this->asUser()->getJson("api/profile");

        // Asserts
        $response->assertOk();

        $response->assertJson(['data' => [
            'name' => $this->user->name,
            'main_category_id' => $this->user->mainCategory->id,
            'read_later_collection_id' => $this->user->readLaterCollection->id,
        ]]);

        $response->assertJsonStructure(['data' => [
            'name',
            'main_category_id',
            'read_later_collection_id',
        ]]);
    }
}
