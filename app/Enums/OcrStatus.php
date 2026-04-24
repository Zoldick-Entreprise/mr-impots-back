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
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
