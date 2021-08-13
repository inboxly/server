<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EntryResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;

class EntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = $request->user()->entries()
            ->with(['original', 'feed.original', 'collections'])
            ->when(
                $request->has('unreadOnly'),
                fn(Builder $builder) => $builder->whereNull('read_at')
            )
            ->when(
                $request->has('readOnly'),
                fn(Builder $builder) => $builder->whereNotNull('read_at')
            )
            ->when(
                $request->has('todayOnly'),
                // todo: use date of creating an original entry instead
                fn(Builder $builder) => $builder->where('created_at', '>=', Carbon::today())
            )
            ->when(
                $request->has('oldest'),
                // todo: use date of creating an original entry instead
                fn(Builder $builder) => $builder->oldest('created_at'),
                fn(Builder $builder) => $builder->latest('created_at')
            );

        $entries = $builder->cursorPaginate()->withQueryString();

        return EntryResource::collection($entries);
    }
}
