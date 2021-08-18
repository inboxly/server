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

    // Main
    Route::get('entries', EntriesController::class);
    Route::post('explore/{explorerKey?}', ExploreController::class);

    // Feed
    Route::get('feeds', [FeedsController::class, 'index']);
    Route::get('feeds/counts', FeedsCountsController::class);
    Route::get('feeds/{feedByOriginalFeedId}', [FeedsController::class, 'show']);
    Route::put('feeds/{originalFeed}/subscription', [FeedSubscriptionController::class, 'subscribe']);
    Route::delete('feeds/{feedByOriginalFeedId}/subscription', [FeedSubscriptionController::class, 'unsubscribe']);
    Route::post('feeds/{feedByOriginalFeedId}/categories', [FeedCategoriesController::class, 'store']);
    Route::delete('feeds/{feedByOriginalFeedId}/categories', [FeedCategoriesController::class, 'destroy']);
    Route::get('feeds/{feedByOriginalFeedId}/entries', FeedEntriesController::class);

    // Category
    Route::get('categories', [CategoriesController::class, 'index']);
    Route::post('categories', [CategoriesController::class, 'store']);
    Route::put('categories/{category}', [CategoriesController::class, 'update']);
    Route::delete('categories/{category}', [CategoriesController::class, 'destroy']);
    Route::get('categories/{category}/entries', CategoryEntriesController::class);
    Route::get('categories/{category}/feeds', [CategoryFeedsController::class, 'index']);
    Route::post('categories/{category}/feeds', [CategoryFeedsController::class, 'store']);
    Route::delete('categories/{category}/feeds', [CategoryFeedsController::class, 'destroy']);

    // Collection
    Route::get('collections', [CollectionsController::class, 'index']);
    Route::post('collections', [CollectionsController::class, 'store']);
    Route::put('collections/{collection}', [CollectionsController::class, 'update']);
    Route::delete('collections/{collection}', [CollectionsController::class, 'destroy']);
    Route::get('collections/{collection}/entries', [CollectionEntriesController::class, 'index']);
    Route::post('collections/{collection}/entries', [CollectionEntriesController::class, 'store']);
    Route::delete('collections/{collection}/entries', [CollectionEntriesController::class, 'destroy']);

    // Saved
    Route::get('saved/entries', [SavedEntriesController::class, 'index']);
    Route::post('saved/entries', [SavedEntriesController::class, 'store']);
    Route::delete('saved/entries', [SavedEntriesController::class, 'destroy']);

    // Read
    Route::post('read/entries', [ReadEntriesController::class, 'store']);
    Route::delete('read/entries', [ReadEntriesController::class, 'destroy']);
    Route::post('read/all', [ReadController::class, 'all']);
    Route::post('read/categories/{category}', [ReadController::class, 'category']);
    Route::post('read/collections/{collection}', [ReadController::class, 'collection']);
    Route::post('read/feeds/{feedByOriginalFeedId}', [ReadController::class, 'feed']);
    Route::post('read/saved', [ReadController::class, 'saved']);

});
