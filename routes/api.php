<?php

declare(strict_types=1);

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CategoryEntriesController;
use App\Http\Controllers\CategoryFeedsController;
use App\Http\Controllers\CollectionEntriesController;
use App\Http\Controllers\CollectionsController;
use App\Http\Controllers\EntriesController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedCategoriesController;
use App\Http\Controllers\FeedEntriesController;
use App\Http\Controllers\FeedsController;
use App\Http\Controllers\FeedsCountsController;
use App\Http\Controllers\FeedSubscriptionController;
use App\Http\Controllers\ReadController;
use App\Http\Controllers\ReadEntriesController;
use App\Http\Controllers\SavedEntriesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('categories', CategoriesController::class)->except(['show']);
    Route::get('categories/{category}/entries', [CategoryEntriesController::class, 'index'])
        ->name('categories.entries.index');
    Route::get('categories/{category}/feeds', [CategoryFeedsController::class, 'index'])
        ->name('categories.feeds.index');
    Route::post('categories/{category}/feeds', [CategoryFeedsController::class, 'store'])
        ->name('categories.feeds.store');
    Route::delete('categories/{category}/feeds', [CategoryFeedsController::class, 'destroy'])
        ->name('categories.feeds.destroy');
    Route::apiResource('collections', CollectionsController::class)->except(['show']);
    Route::get('collections/{collection}/entries', [CollectionEntriesController::class, 'index'])
        ->name('collections.entries.index');
    Route::post('collections/{collection}/entries', [CollectionEntriesController::class, 'store'])
        ->name('collections.entries.store');
    Route::delete('collections/{collection}/entries', [CollectionEntriesController::class, 'destroy'])
        ->name('collections.entries.destroy');
    Route::get('saved/entries', [SavedEntriesController::class, 'index'])
        ->name('saved.entries.index');
    Route::post('saved/entries', [SavedEntriesController::class, 'store'])
        ->name('saved.entries.store');
    Route::delete('saved/entries', [SavedEntriesController::class, 'destroy'])
        ->name('saved.entries.destroy');
    Route::get('entries', [EntriesController::class, 'index'])
        ->name('entries.index');
    Route::post('read/entries', [ReadEntriesController::class, 'store'])
        ->name('read.entries.store');
    Route::delete('read/entries', [ReadEntriesController::class, 'destroy'])
        ->name('read.entries.destroy');
    Route::post('read/all', [ReadController::class, 'all'])
        ->name('read.all');
    Route::post('read/categories/{category}', [ReadController::class, 'category'])
        ->name('read.category');
    Route::post('read/collections/{collection}', [ReadController::class, 'collection'])
        ->name('read.collection');
    Route::post('read/feeds/{feedByOriginalFeedId}', [ReadController::class, 'feed'])
        ->name('read.feed');
    Route::post('read/saved', [ReadController::class, 'saved'])
        ->name('read.saved');
    Route::post('feeds/{feedByOriginalFeedId}/categories', [FeedCategoriesController::class, 'store'])
        ->name('feeds.categories.store');
    Route::delete('feeds/{feedByOriginalFeedId}/categories', [FeedCategoriesController::class, 'destroy'])
        ->name('feeds.categories.destroy');
    Route::get('feeds/counts', [FeedsCountsController::class, 'index'])
        ->name('feeds.counts.index');
    Route::get('feeds/{feedByOriginalFeedId}/entries', [FeedEntriesController::class, 'index'])
        ->name('feeds.entries.index');
    Route::get('feeds', [FeedsController::class, 'index'])
        ->name('feeds.index');
    Route::get('feeds/{feedByOriginalFeedId}', [FeedsController::class, 'show'])
        ->name('feeds.show');
    Route::post('explore/{explorerKey?}', [ExploreController::class, 'explore'])
        ->name('explorer.explore');
    Route::put('feeds/{originalFeed}/subscription', [FeedSubscriptionController::class, 'subscribe'])
        ->name('feeds.subscribe');
    Route::delete('feeds/{feedByOriginalFeedId}/subscription', [FeedSubscriptionController::class, 'unsubscribe'])
        ->name('feeds.unsubscribe');
});
