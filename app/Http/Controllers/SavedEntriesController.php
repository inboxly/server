<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchEntriesRequest;
use App\Http\Resources\EntryResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class SavedEntriesController extends Controller
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

        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $user->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->whereNotNull('saved_at')
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
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(BatchEntriesRequest $request): Response
    {
        $request->user()->entries()
            ->whereIn('id', $request->ids())
            ->whereNull('saved_at')
            ->update(['saved_at' => now()]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param \App\Http\Requests\BatchEntriesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(BatchEntriesRequest $request): Response
    {
        $request->user()->entries()
            ->whereIn('id', $request->ids())
            ->whereNotNull('saved_at')
            ->update(['saved_at' => null]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
