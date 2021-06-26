<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchEntriesRequest;
use App\Http\Resources\EntryResource;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class ReadEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * todo: Delete this method. (use instead: /api/entries?readOnly=1)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $builder = $user->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->whereNotNull('read_at');

        $builder = $request->has('oldest')
            ? $builder->oldest('read_at')
            : $builder->latest('read_at');

        /** @noinspection PhpUndefinedMethodInspection */
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
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

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
        Entry::query()
            ->whereIn('id', $request->ids())
            ->whereNotNull('read_at')
            ->update(['read_at' => null]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
