<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\OriginalEntry;

class OriginalEntryObserver
{
    /**
     * Handle the OriginalEntry "saving" event.
     *
     * @param  \App\Models\OriginalEntry  $originalEntry
     * @return void
     */
    public function saving(OriginalEntry $originalEntry): void
    {
        // Recalculate entry hash
        $originalEntry->hash = $originalEntry->getHash();
    }
}
