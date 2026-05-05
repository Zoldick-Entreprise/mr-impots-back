<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum OcrStatus
 *
 * Represents the background OCR processing state of a document.
 */
enum OcrStatus: string
{
    /**
     * OCR processing has not started yet.
     */
    case PENDING = 'ocrs_pending';

    /**
     * OCR processing is currently in progress.
     */
    case PROCESSING = 'ocrs_processing';

    /**
     * OCR processing has completed successfully.
     */
    case COMPLETED = 'ocrs_completed';

    /**
     * OCR processing has failed.
     */
    case FAILED = 'ocrs_failed';
}
