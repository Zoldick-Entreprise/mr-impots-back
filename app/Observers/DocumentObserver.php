<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ProcessDocumentOcr;
use App\Models\Document;

final class DocumentObserver
{
    public bool $afterCommit = true;

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        ProcessDocumentOcr::dispatch($document->id, 'fr');
        ProcessDocumentOcr::dispatch($document->id, 'en');
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        //
    }
}
