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
use App\Http\Controllers\ReadStatesController;
use Illuminate\Support\Facades\Route;

// Main
Route::get('entries', EntriesController::class);
Route::post('explore', ExploreController::class);

// Category
Route::get('categories', [CategoriesController::class, 'index']);
Route::post('categories', [CategoriesController::class, 'store']);
Route::put('categories/{category}', [CategoriesController::class, 'update']);
Route::delete('categories/{category}', [CategoriesController::class, 'destroy']);
Route::get('categories/{category}/feeds', [CategoryFeedsController::class, 'index']);
Route::post('categories/{category}/feeds', [CategoryFeedsController::class, 'store']);
Route::delete('categories/{category}/feeds', [CategoryFeedsController::class, 'destroy']);
Route::get('categories/{category}/entries', CategoryEntriesController::class);

// Collection
Route::get('collections', [CollectionsController::class, 'index']);
Route::post('collections', [CollectionsController::class, 'store']);
Route::put('collections/{collection}', [CollectionsController::class, 'update']);
Route::delete('collections/{collection}', [CollectionsController::class, 'destroy']);
Route::get('collections/{collection}/entries', [CollectionEntriesController::class, 'index']);
Route::post('collections/{collection}/entries', [CollectionEntriesController::class, 'store']);
Route::delete('collections/{collection}/entries', [CollectionEntriesController::class, 'destroy']);

// Feed
Route::get('feeds/counts', FeedsCountsController::class);
Route::get('feeds', [FeedsController::class, 'index']);
Route::get('feeds/{feed}', [FeedsController::class, 'show']);
Route::post('feeds/{feed}/categories', [FeedCategoriesController::class, 'store']);
Route::delete('feeds/{feed}/categories', [FeedCategoriesController::class, 'destroy']);
Route::get('feeds/{feed}/entries', FeedEntriesController::class);

// ReadState
Route::post('read/all', [ReadStatesController::class, 'all']);
Route::post('read/categories/{category}', [ReadStatesController::class, 'category']);
Route::post('read/collections/{collection}', [ReadStatesController::class, 'collection']);
Route::post('read/feeds/{feed}', [ReadStatesController::class, 'feed']);
Route::post('read/entries', [ReadStatesController::class, 'entries']);
Route::delete('read/entries', [ReadStatesController::class, 'destroy']);
