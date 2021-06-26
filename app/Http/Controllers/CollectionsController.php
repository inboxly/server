<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CollectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return CollectionResource::collection($user->collections);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreCollectionRequest $request
     * @return \Illuminate\Http\Resources\Json\JsonResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreCollectionRequest $request): JsonResource
    {
        $this->authorize('create', Collection::class);

        $collection = $request->user()->collections()->create(
            $request->validated(),
        );

        return CollectionResource::make($collection);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateCollectionRequest $request
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateCollectionRequest $request, Collection $collection): Response
    {
        $this->authorize('update', $collection);

        $collection->update($request->validated());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Collection $collection): Response
    {
        $this->authorize('delete', $collection);

        $collection->delete();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
