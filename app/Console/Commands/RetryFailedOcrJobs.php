<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ProcessDocumentOcr;
use App\Models\OcrJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ocr:retry-failed')]
#[Description('Retry all failed OCR jobs')]
class RetryFailedOcrJobs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $failedJobs = OcrJob::where('status', 'failed')->get();

        if ($failedJobs->isEmpty()) {
            $this->info('No failed OCR jobs found.');

            return;
        }

        $this->info("Found {$failedJobs->count()} failed jobs. Retrying...");

        foreach ($failedJobs as $job) {
            $job->update(['status' => 'pending', 'error_message' => null]);
            // Re-dispatch the job
            ProcessDocumentOcr::dispatch($job->document_id);
            $this->line(
                "Dispatched OCR job for Document ID: {$job->document_id}",
            );
        }

        $this->info('All failed jobs have been re-dispatched.');
    }
}
