<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchEntriesRequest;
use App\Http\Resources\EntryResource;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CollectionEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Collection $collection): ResourceCollection
    {
        $this->authorize('view', $collection);

        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $collection->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->when(
                $request->has('oldest'),
                // todo: use date of creating an original entry instead
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            );

        $entries = $builder->cursorPaginate()->withQueryString();

        return EntryResource::collection($entries);
    }

    /**
     * @param \App\Models\Collection $collection
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Collection $collection, BatchEntriesRequest $request): Response
    {
        $this->authorize('update', $collection);

        $collection->entries()->syncWithoutDetaching(
            $request->ids()
        );

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Models\Collection $collection
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Collection $collection, BatchEntriesRequest $request): Response
    {
        $this->authorize('update', $collection);

        $collection->entries()->detach($request->ids());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
