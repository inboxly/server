<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ReadController
 */
class ReadControllerTest extends TestCase
{
    private EloquentCollection $allEntries;
    private Category $category;
    private EloquentCollection $categoryEntries;
    private Feed $feed;
    private EloquentCollection $feedEntries;

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::all()
     */
    public function can_add_all_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->postJson("api/read/all");

        // Asserts
        $response->assertNoContent();

        $entries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertSame($entries->modelKeys(), $this->allEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::all()
     */
    public function can_add_all_today_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();
        $oldEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $this->feed->getKey(),
            'created_at' => fn () => Carbon::today()->subWeek(),
        ]);

        // Run
        $response = $this->asUser()->postJson("api/read/all?todayOnly=1");

        // Asserts
        $response->assertNoContent();

        $entries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertSame($entries->modelKeys(), $this->allEntries->modelKeys());

        $unreadEntries = $this->user->entries()->whereNull('read_at')->get();
        $this->assertSame($unreadEntries->modelKeys(), $oldEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::feed()
     */
    public function can_add_feed_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->postJson("api/read/feeds/{$this->feed->original_feed_id}");

        // Asserts
        $response->assertNoContent();

        $entries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertSame($entries->modelKeys(), $this->feedEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::feed()
     */
    public function cannot_add_not_its_feed_entries_to_read(): void
    {
        // Setup
        /** @var Feed $feed */
        $feed = Feed::factory()->create();

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'feed_id' => $feed->getKey(),
            'user_id' => $feed->user_id,
        ]);

        // Run
        $response = $this->asUser()->postJson("api/read/feeds/{$feed->original_feed_id}");

        // Asserts
        $response->assertNotFound();

        $this->assertNull($entry->refresh()->read_at);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::category()
     */
    public function can_add_category_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->postJson("api/read/categories/{$this->category->getKey()}");

        // Asserts
        $response->assertNoContent();

        $entries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertSame($entries->modelKeys(), $this->categoryEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::category()
     */
    public function cannot_add_not_its_category_entries_to_read(): void
    {
        // Setup

        /** @var Category $category */
        $category = Category::factory()->create();

        /** @var Feed $feed */
        $feed = Feed::factory()->create([
            'user_id' => $category->user_id,
        ]);
        $feed->categories()->attach($category);

        /** @var Entry $entry */
        $entry = Entry::factory()->create([
            'feed_id' => $feed->getKey(),
            'user_id' => $feed->user_id,
        ]);

        // Run
        $response = $this->asUser()->postJson("api/read/categories/{$category->getKey()}");

        // Asserts
        $response->assertForbidden();

        $this->assertNull($entry->refresh()->read_at);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadController::saved()
     */
    public function can_add_saved_entries_to_read(): void
    {
        // Setup
        $this->prepareEntries();
        $savedEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $this->feed->getKey(),
            'saved_at' => fn() => Carbon::now(),
        ]);

        // Run
        $response = $this->asUser()->postJson("api/read/saved");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->entries()->whereNotNull('read_at')->get();
        $this->assertSame($readEntries->modelKeys(), $savedEntries->modelKeys());

        $unreadEntries = $this->user->entries()->whereNull('read_at')->get();
        $this->assertSame($unreadEntries->modelKeys(), $this->allEntries->modelKeys());
    }

    protected function prepareEntries()
    {
        // Feed entries
        $this->feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->feedEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $this->feed->getKey(),
        ]);

        // Category entries
        $this->categoryEntries = $this->feedEntries->merge(Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => ($categoryFeed = Feed::factory()->create(['user_id' => $this->user->getKey()]))->getKey(),
        ]));
        $this->category = Category::factory()->create(['user_id' => $this->user->getKey()]);
        $this->category->feeds()->syncWithoutDetaching([$this->feed->getKey(), $categoryFeed->getKey()]);

        // All entries
        $this->allEntries = $this->categoryEntries->merge(Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => Feed::factory()->create(['user_id' => $this->user->getKey()])->getKey(),
        ]));
    }

    protected function entryStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
