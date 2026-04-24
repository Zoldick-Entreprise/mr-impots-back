<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum DocumentStatus
 *
 * Represents the publication lifecycle state of a document.
 */
enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
