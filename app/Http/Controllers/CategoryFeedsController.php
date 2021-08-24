<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchFeedsRequest;
use App\Http\Resources\FeedResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CategoryFeedsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Category $category): ResourceCollection
    {
        $this->authorize('view', $category);

        $category->load(['feeds.userCategories']);

        return FeedResource::collection($category->feeds);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\BatchFeedsRequest $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(BatchFeedsRequest $request, Category $category): Response
    {
        $this->authorize('manageFeeds', $category);

        DB::beginTransaction();

        $category->feeds()->syncWithoutDetaching($request->ids());

        $request->user()->subscribedFeeds()->syncWithoutDetaching($request->ids());

        DB::commit();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Http\Requests\BatchFeedsRequest $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function destroy(BatchFeedsRequest $request, Category $category): Response
    {
        $this->authorize('manageFeeds', $category);

        DB::beginTransaction();

        $category->feeds()->detach($request->ids());

        $request->user()->subscribedFeeds()->whereDoesntHave('userCategories')->detach($request->ids());

        DB::commit();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
