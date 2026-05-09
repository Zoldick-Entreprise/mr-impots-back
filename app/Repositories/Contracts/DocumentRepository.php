<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Document;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface DocumentRepository
 *
 * Defines the specific contract for Document data access operations, extending
 * the base repository contract for common CRUD operations.
 *
 * @method Collection<int, Document> notDeleted()
 *
 * @extends Repository<Document>
 */
interface DocumentRepository extends Repository
{
    /**
     * Retrieve a paginated or unpaginated list of strictly published documents for public access,
     * applying optional HTTP queries (filters, sorts, pagination).
     *
     * @param  array<string, mixed>  $queries  The associative array of query parameters.
     * @return Collection<int, Document>|Paginator The collection of published documents.
     */
    public function getPublished(array $queries = []): Collection|Paginator;

    /**
     * Toggle the publication status of a specific document (draft <-> published).
     * Automatically updates the 'published_at' timestamp accordingly.
     *
     * @param  Document|string  $document  The document model instance or ID to toggle.
     * @return Document The updated document model.
     */
    public function togglePublish(Document|string $document): Document;

    /**
     * Retrieve a paginated or unpaginated list of non archived documents for all access,
     * applying optional HTTP queries (filters, sorts, pagination).
     *
     * @param  array<string, mixed>  $queries  The associative array of query parameters.
     * @return Collection<int, Document>|Paginator The collection of non archived documents.
     */
    public function getNonArchived(array $queries = []): Collection|Paginator;
}
