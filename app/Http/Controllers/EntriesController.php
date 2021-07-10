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
        /** @var Builder $builder */
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
            );

        $builder = $request->has('oldest')
            ? $builder->oldest('created_at')
            : $builder->latest('created_at');

        /**
         * Hotfix for ignore ide warnings.
         * Delete this when the 'cursorPaginate()' method will
         * return the interface with the 'withQueryString() method.
         * @var \Illuminate\Pagination\AbstractPaginator $paginator
         */
        $paginator = $builder->cursorPaginate();
        $entries = $paginator->withQueryString();

        return EntryResource::collection($entries);
    }
}
