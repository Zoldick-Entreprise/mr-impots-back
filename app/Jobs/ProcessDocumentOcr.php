<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\DocumentStatus;
use App\Enums\OcrStatus;
use App\Models\Document;
use App\Models\DocumentPage;
use App\Models\OcrJob;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ProcessDocumentOcr implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public $backoff = [30, 120, 300];

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $documentId,
        public readonly string $locale = 'fr',
    ) {}

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->documentId.'_'.$this->locale;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $document = Document::find($this->documentId);

        if (! $document) {
            return;
        }

        // Initialize or update OcrJob
        $ocrJob = OcrJob::updateOrCreate(
            ['document_id' => $document->id, 'locale' => $this->locale],
            [
                'status' => OcrStatus::PROCESSING,
                'started_at' => now(),
                'error_message' => null,
            ],
        );

        $document->update(['ocr_status' => OcrStatus::PROCESSING]);

        try {
            // Retrieve the media object
            $mediaCollection = 'document_'.$this->locale;
            /** @var Media $media */
            $media = $document->getFirstMedia($mediaCollection);

            if (! $media) {
                throw new Exception(
                    "No PDF file found for document {$document->id} in locale {$this->locale}.",
                );
            }

            // 1. Download to temporary local file
            $tempPath = storage_path(
                'app/temp/'.uniqid('ocr_', true).'.pdf',
            );
            if (! file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            // In a real S3/R2 scenario, copy the stream
            $stream = $media->stream();
            file_put_contents($tempPath, stream_get_contents($stream));

            // 2. Parse using smalot/pdfparser
            $parser = new Parser;
            $pdf = $parser->parseFile($tempPath);
            $pages = $pdf->getPages();

            $pagesData = [];
            foreach ($pages as $index => $page) {
                $text = trim($page->getText());

                // If text is totally empty, it might be a scanned PDF (requires Google Cloud Vision later)
                // For now, we only handle the native extraction part.

                if (! empty($text)) {
                    $pagesData[] = [
                        'id' => (string) Str::uuid(),
                        'document_id' => $document->id,
                        'locale' => $this->locale,
                        'page_number' => $index + 1,
                        'content' => $text,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 3. Batch insert pages
            if (! empty($pagesData)) {
                DB::transaction(function () use ($document, $pagesData) {
                    // Clear old pages in case of re-run
                    DocumentPage::where('document_id', $document->id)
                        ->where('locale', $this->locale)
                        ->delete();

                    // Insert all pages efficiently
                    foreach (array_chunk($pagesData, 500) as $chunk) {
                        DocumentPage::insert($chunk);
                    }
                });
            }

            // 4. Update Statuses
            $ocrJob->update([
                'status' => OcrStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $document->update([
                'ocr_status' => OcrStatus::COMPLETED,
                'status' => DocumentStatus::PUBLISHED,
                'published_at' => now(),
            ]);

            // Cleanup
            @unlink($tempPath);
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error(
            "OCR Job failed for document {$this->documentId}: ".
                $exception->getMessage(),
        );

        $ocrJob = OcrJob::where('document_id', $this->documentId)
            ->where('locale', $this->locale)
            ->first();
        if ($ocrJob) {
            $ocrJob->update([
                'status' => OcrStatus::FAILED,
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
        }

        Document::where('id', $this->documentId)->update([
            'ocr_status' => OcrStatus::FAILED,
        ]);
    }
}
