<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ExploreController
 */
class ExploreControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::__invoke()
     */
    public function user_can_explore_feeds_from_reddit(): void
    {
        $this->markTestSkipped('Test skipped because it use external service');

        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://www.reddit.com/r/kdeneon',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'name' => 'KDE Neon',
                'url' => 'https://www.reddit.com/r/kdeneon',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::__invoke()
     */
    public function user_can_explore_feeds_from_reddit_by_query(): void
    {
        $this->markTestSkipped('Test skipped because it use external service');

        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'alienth',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'name' => 'overview for alienth',
                'url' => 'https://www.reddit.com/user/alienth',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::__invoke()
     */
    public function user_can_explore_feeds_from_github(): void
    {
        $this->markTestSkipped('Test skipped because it use external service');

        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://github.com/alexdebril/feed-io/commits/main',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'name' => 'Recent Commits to feed-io:main',
                'url' => 'https://github.com/alexdebril/feed-io/commits/main',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::__invoke()
     */
    public function user_can_explore_feeds_from_youtube(): void
    {
        $this->markTestSkipped('Test skipped because it use external service');

        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://www.youtube.com/channel/UCmRBQ7JshWJss0hZnj3K_Bg',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'name' => 'Luke Diebold',
                'url' => 'https://www.youtube.com/channel/UCmRBQ7JshWJss0hZnj3K_Bg',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);
    }

    protected function feedStructure(): array
    {
        return require base_path('tests/fixtures/feed-structure.php');
    }
}
