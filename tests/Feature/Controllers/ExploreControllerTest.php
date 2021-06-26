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
     * @see \App\Http\Controllers\ExploreController::explore()
     */
    public function can_explore_feeds_from_reddit(): void
    {
        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://www.reddit.com/r/kdeneon',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'title' => 'KDE Neon',
                'link' => 'https://www.reddit.com/r/kdeneon',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);

        $this->markTestIncomplete('Need improving test for use Mocks');
    }
    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::explore()
     */
    public function can_explore_feeds_from_reddit_by_query(): void
    {
        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'alienth',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'title' => 'overview for alienth',
                'link' => 'https://www.reddit.com/user/alienth',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);

        $this->markTestIncomplete('Need improving test for use Mocks');
    }

    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::explore()
     */
    public function can_explore_feeds_from_github(): void
    {
        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://github.com/alexdebril/feed-io/commits/main',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'title' => 'Recent Commits to feed-io:main',
                'link' => 'https://github.com/alexdebril/feed-io/commits/main',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);

        $this->markTestIncomplete('Need improving test for use Mocks');
    }

    /**
     * @test
     * @see \App\Http\Controllers\ExploreController::explore()
     */
    public function can_explore_feeds_from_youtube(): void
    {
        // Run
        $response = $this->asUser()->postJson("api/explore", [
            'query' => 'https://www.youtube.com/channel/UCmRBQ7JshWJss0hZnj3K_Bg',
        ]);

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $response->assertJson(['data' => [
            [
                'title' => 'Luke Diebold',
                'link' => 'https://www.youtube.com/channel/UCmRBQ7JshWJss0hZnj3K_Bg',
            ]
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->feedStructure()
        ]]);

        $this->markTestIncomplete('Need improving test for use Mocks');
    }

    protected function feedStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/original-feed-structure.php');
    }
}
