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
     */
    public function index(Request $request, Collection $collection): ResourceCollection
    {
        $entries = $collection->entries()
            ->with(['userCollections', 'userReadState', 'feed.userCategories'])
            ->when(
                $request->has('oldest'),
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            )
            ->cursorPaginate();

        return EntryResource::collection($entries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(BatchEntriesRequest $request, Collection $collection): Response
    {
        $this->authorize('manageEntries', $collection);

        $collection->entries()->syncWithoutDetaching($request->ids());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @param \App\Models\Collection $collection
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(BatchEntriesRequest $request, Collection $collection): Response
    {
        $this->authorize('manageEntries', $collection);

        $collection->entries()->detach($request->ids());

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
