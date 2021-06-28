<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BatchEntriesRequest;
use App\Models\Entry;
use Illuminate\Http\Response;

class ReadEntriesController extends Controller
{
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
