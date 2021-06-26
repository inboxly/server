<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CategoryEntriesController
 */
class CategoryEntriesControllerTest extends TestCase
{
    private Category $category;
    private EloquentCollection $readEntries;
    private EloquentCollection $unreadEntries;
    private EloquentCollection $otherReadEntries;
    private EloquentCollection $otherUnreadEntries;

    /**
     * @test
     * @see \App\Http\Controllers\CategoryEntriesController::index()
     */
    public function can_get_list_of_all_entries_in_category(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/categories/{$this->category->id}/entries");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryEntriesController::index()
     */
    public function can_get_reversed_list_of_all_entries_in_category(): void
    {
        // Setup
        $this->prepareEntries();

        // Run
        $response = $this->asUser()->getJson("api/categories/{$this->category->id}/entries?oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(4, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherUnreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryEntriesController::index()
     */
    public function can_get_list_of_all_unread_entries_in_category(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/categories/{$this->category->id}/entries?unreadOnly=1");

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
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherReadEntries->first()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\CategoryEntriesController::index()
     */
    public function can_get_reversed_list_of_all_unread_entries_in_category(): void
    {
        $this->prepareEntries();

        $response = $this->asUser()->getJson("api/categories/{$this->category->id}/entries?unreadOnly=1&oldest=1");

        // Asserts
        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJson(['data' => [
            ['id' => $this->unreadEntries->first()->getKey()],
            ['id' => $this->unreadEntries->last()->getKey()],
        ]]);

        $response->assertJsonMissing(['data' => [
            ['id' => $this->otherReadEntries->first()->getKey()],
            ['id' => $this->otherReadEntries->last()->getKey()],
            ['id' => $this->otherUnreadEntries->first()->getKey()],
            ['id' => $this->otherUnreadEntries->last()->getKey()],
            ['id' => $this->readEntries->first()->getKey()],
            ['id' => $this->readEntries->last()->getKey()],
        ]]);

        $response->assertJsonStructure(['data' => [
            $this->entryStructure(),
        ]]);
    }


    /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
    protected function prepareEntries()
    {
        $categories = Category::factory(2)->create(['user_id' => $this->user->getKey()]);

        // Main models
        $feed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->readEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);
        $this->unreadEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $feed->getKey(),
        ]);
        $this->category = $categories->first();
        $this->category->feeds()->sync($feed);

        /** @var Collection $collection */
        $collection = Collection::factory()->create(['user_id' => $this->user->getKey()]);
        $collection->entries()->syncWithoutDetaching($this->readEntries);
        $collection->entries()->syncWithoutDetaching($this->unreadEntries);

        // Other models
        $otherFeed = Feed::factory()->create(['user_id' => $this->user->getKey()]);
        $this->otherReadEntries = Entry::factory(2)->read()->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $otherFeed->getKey(),
        ]);
        $this->otherUnreadEntries = Entry::factory(2)->create([
            'user_id' => $this->user->getKey(),
            'feed_id' => $otherFeed->getKey(),
        ]);
        /** @var Category $otherCategory */
        $otherCategory = $categories->last();
        $otherCategory->feeds()->sync($otherFeed);
    }

    protected function entryStructure(): array
    {
        /** @noinspection PhpIncludeInspection */
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
