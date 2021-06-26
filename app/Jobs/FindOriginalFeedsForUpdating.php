<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\OriginalFeed;
use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class FindOriginalFeedsForUpdating implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     * @return void
     */
    public function handle(Dispatcher $dispatcher): void
    {
        $now = Carbon::now()->toDateTimeString('microsecond');

        OriginalFeed::query()
            ->whereNotNull('next_update_at')
            ->where('next_update_at', '<=', $now)
            ->each(function (OriginalFeed $originalFeed) use ($dispatcher) {
                $dispatcher->dispatch(new UpdateOriginalFeed($originalFeed));
            });
    }
}
