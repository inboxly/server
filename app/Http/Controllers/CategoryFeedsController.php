<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchFeedsRequest;
use App\Http\Resources\FeedResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

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

        $category->load(['feeds.original', 'feeds.categories']);

        return FeedResource::collection($category->feeds);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Models\Category $category
     * @param \App\Http\Requests\BatchFeedsRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Category $category, BatchFeedsRequest $request): Response
    {
        $this->authorize('update', $category);

        $category->feeds()->syncWithoutDetaching($request->ids());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Models\Category $category
     * @param \App\Http\Requests\BatchFeedsRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Category $category, BatchFeedsRequest $request): Response
    {
        $this->authorize('update', $category);

        $category->feeds()->detach($request->ids());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
