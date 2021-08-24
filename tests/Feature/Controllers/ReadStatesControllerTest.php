<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Entry;
use App\Models\Feed;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ReadStatesController
 */
class ReadStatesControllerTest extends TestCase
{
    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::all()
     */
    public function user_can_add_all_entries_to_read(): void
    {
        // Setup
        $entries = Entry::factory(4)->create();
        $this->user->subscribedFeeds()->sync($entries->pluck('feed_id'));
        foreach ($entries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' =>$entry->feed_id]);
        }

        $unsubscribedCollection = Collection::factory()->create(['user_id' => $this->user->id]);
        $unsubscribedCollectionEntries = Entry::factory(4)->create();
        $unsubscribedCollection->entries()->sync($unsubscribedCollectionEntries);
        foreach ($unsubscribedCollectionEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' =>$entry->feed_id]);
        }

        $otherUnsubscribedEntries = Entry::factory(4)->create();
        foreach ($otherUnsubscribedEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' =>$entry->feed_id]);
        }

        // Run
        $response = $this->asUser()->postJson("api/read/all");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), $entries->modelKeys());

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), [
            ...$unsubscribedCollectionEntries->modelKeys(),
            ...$otherUnsubscribedEntries->modelKeys(),
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::all()
     */
    public function user_can_add_all_today_entries_to_read(): void
    {
        // Setup
        $todayEntries = Entry::factory(4)->today()->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($todayEntries->pluck('feed_id'));
        foreach ($todayEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' => $entry->feed_id]);
        }

        $oldEntries = Entry::factory(4)->create();
        $this->user->subscribedFeeds()->syncWithoutDetaching($oldEntries->pluck('feed_id'));
        foreach ($oldEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' => $entry->feed_id]);
        }

        $unsubscribedCollection = Collection::factory()->create(['user_id' => $this->user->id]);
        $unsubscribedCollectionEntries = Entry::factory(4)->create();
        $unsubscribedCollection->entries()->sync($unsubscribedCollectionEntries);
        foreach ($unsubscribedCollectionEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' => $entry->feed_id]);
        }

        $otherUnsubscribedEntries = Entry::factory(4)->create();
        foreach ($otherUnsubscribedEntries as $entry) {
            $this->user->entries()->attach($entry, ['feed_id' => $entry->feed_id]);
        }

        // Run
        $response = $this->asUser()->postJson("api/read/all?todayOnly=1");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), $todayEntries->modelKeys());

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), [
            ...$oldEntries->modelKeys(),
            ...$unsubscribedCollectionEntries->modelKeys(),
            ...$otherUnsubscribedEntries->modelKeys(),
        ]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::feed()
     */
    public function user_can_add_feed_entries_to_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed);
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $this->user->entries()->syncWithPivotValues($feedEntries, ['feed_id' => $feed->id], false);

        $otherFeed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($otherFeed);
        $otherFeedEntries = Entry::factory(2)->create(['feed_id' => $otherFeed->getKey()]);
        $this->user->entries()->syncWithPivotValues($otherFeedEntries, ['feed_id' => $otherFeed->id], false);

        // Run
        $response = $this->asUser()->postJson("api/read/feeds/$feed->id");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), $feedEntries->modelKeys());

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), $otherFeedEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::category()
     */
    public function user_can_add_category_entries_to_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed);
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $this->user->entries()->syncWithPivotValues($feedEntries, ['feed_id' => $feed->id], false);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $category->feeds()->attach($feed);

        $otherFeed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($otherFeed);
        $otherFeedEntries = Entry::factory(2)->create(['feed_id' => $otherFeed->getKey()]);
        $this->user->entries()->syncWithPivotValues($otherFeedEntries, ['feed_id' => $otherFeed->id], false);
        $otherCategory = Category::factory()->create(['user_id' => $this->user->id]);
        $otherCategory->feeds()->attach($otherFeed);

        // Run
        $response = $this->asUser()->postJson("api/read/categories/{$category->getKey()}");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), $feedEntries->modelKeys());

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), $otherFeedEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::category()
     */
    public function user_cannot_add_not_its_category_entries_to_read(): void
    {
        // Setup
        /** @var Category $category */
        $category = Category::factory()->create();

        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        $feed->categories()->attach($category);
        Entry::factory()->create(['feed_id' => $feed->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/read/categories/{$category->getKey()}");

        // Asserts
        $response->assertForbidden();
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::collection()
     */
    public function user_can_add_collection_entries_to_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed);
        $collection = Collection::factory()->create(['user_id' => $this->user->id]);
        $collectionEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $collection->entries()->attach($collectionEntries);
        $this->user->entries()->syncWithPivotValues($collectionEntries, ['feed_id' => $feed->id], false);

        $otherFeed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($otherFeed);
        $otherCollection = Collection::factory()->create(['user_id' => $this->user->id]);
        $otherFeedEntries = Entry::factory(2)->create(['feed_id' => $otherFeed->getKey()]);
        $otherCollection->entries()->attach($otherFeedEntries);
        $this->user->entries()->syncWithPivotValues($otherFeedEntries, ['feed_id' => $otherFeed->id], false);

        // Run
        $response = $this->asUser()->postJson("api/read/collections/{$collection->getKey()}");

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), $collectionEntries->modelKeys());

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), $otherFeedEntries->modelKeys());
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::collection()
     */
    public function user_cannot_add_not_its_collection_entries_to_read(): void
    {
        // Setup
        /** @var \App\Models\Collection $collection */
        $collection = Collection::factory()->create();

        /** @var Feed $feed */
        $feed = Feed::factory()->create();
        Entry::factory()->create(['feed_id' => $feed->getKey()]);

        // Run
        $response = $this->asUser()->postJson("api/read/collections/{$collection->getKey()}");

        // Asserts
        $response->assertForbidden();
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::entries()
     */
    public function user_can_add_some_subscribed_entries_to_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed);
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $this->user->entries()->syncWithPivotValues($feedEntries, ['feed_id' => $feed->id], false);

        $feed2 = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed2);
        $feed2Entries = Entry::factory(2)->create(['feed_id' => $feed2->getKey()]);
        $this->user->entries()->syncWithPivotValues($feed2Entries, ['feed_id' => $feed2->id], false);

        // Run
        $response = $this->asUser()->postJson('api/read/entries', [
            'ids' => [$feedEntries->first()->id, $feed2Entries->first()->id]
        ]);

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), [$feedEntries->first()->id, $feed2Entries->first()->id]);

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), [$feedEntries->last()->id, $feed2Entries->last()->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::entries()
     */
    public function user_can_add_some_unsubscribed_entries_to_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);

        $feed2 = Feed::factory()->create();
        $feed2Entries = Entry::factory(2)->create(['feed_id' => $feed2->getKey()]);

        // Run
        $response = $this->asUser()->postJson('api/read/entries', [
            'ids' => [$feedEntries->first()->id, $feed2Entries->first()->id]
        ]);

        // Asserts
        $response->assertNoContent();

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), [$feedEntries->first()->id, $feed2Entries->first()->id]);

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), []);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::entries()
     */
    public function user_can_remove_some_subscribed_entries_from_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed);
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $this->user->entries()->syncWithPivotValues(
            $feedEntries, ['feed_id' => $feed->id, 'read_at' => Carbon::now()], false
        );

        $feed2 = Feed::factory()->create();
        $this->user->subscribedFeeds()->attach($feed2);
        $feed2Entries = Entry::factory(2)->create(['feed_id' => $feed2->getKey()]);
        $this->user->entries()->syncWithPivotValues(
            $feed2Entries, ['feed_id' => $feed2->id, 'read_at' => Carbon::now()], false
        );

        // Run
        $response = $this->asUser()->deleteJson('api/read/entries', [
            'ids' => [$feedEntries->first()->id, $feed2Entries->first()->id]
        ]);

        // Asserts
        $response->assertNoContent();

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), [$feedEntries->first()->id, $feed2Entries->first()->id]);

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), [$feedEntries->last()->id, $feed2Entries->last()->id]);
    }

    /**
     * @test
     * @see \App\Http\Controllers\ReadStatesController::entries()
     */
    public function user_can_remove_some_unsubscribed_entries_from_read(): void
    {
        // Setup
        $feed = Feed::factory()->create();
        $feedEntries = Entry::factory(2)->create(['feed_id' => $feed->getKey()]);
        $this->user->entries()->syncWithPivotValues(
            $feedEntries, ['feed_id' => $feed->id, 'read_at' => Carbon::now()], false
        );

        $feed2 = Feed::factory()->create();
        $feed2Entries = Entry::factory(2)->create(['feed_id' => $feed2->getKey()]);
        $this->user->entries()->syncWithPivotValues(
            $feed2Entries, ['feed_id' => $feed2->id, 'read_at' => Carbon::now()], false
        );

        // Run
        $response = $this->asUser()->deleteJson('api/read/entries', [
            'ids' => [$feedEntries->first()->id, $feed2Entries->first()->id]
        ]);

        // Asserts
        $response->assertNoContent();

        $unreadEntries = $this->user->unreadEntries()->oldest()->get();
        $this->assertSame($unreadEntries->modelKeys(), [$feedEntries->first()->id, $feed2Entries->first()->id]);

        $readEntries = $this->user->readEntries()->oldest()->get();
        $this->assertSame($readEntries->modelKeys(), [$feedEntries->last()->id, $feed2Entries->last()->id]);
    }

    protected function entryStructure(): array
    {
        return require base_path('tests/fixtures/entry-structure.php');
    }
}
