<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Repositories\CommonRepository;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DocumentRepositoryEloquent
 *
 * Implements the specific contract for Document data access operations using Eloquent,
 * extending the base repository contract for common CRUD operations.
 *
 * @extends CommonRepository<Document>
 */
final class DocumentRepositoryEloquent extends CommonRepository implements DocumentRepository
{
    /**
     * The Eloquent model representing the Document.
     *
     * @var class-string<Document>
     */
    protected string $model = Document::class;

    public function __construct(array $config = [])
    {
        parent::__construct([
            'includes' => ['category', 'uploadedBy'],
            'relations' => ['category', 'uploadedBy'],
            'sorts' => ['created_at', 'published_at'],
            'filters' => ['status', 'category_id'],
        ]);
    }

    /**
     * Retrieve a paginated or unpaginated list of strictly published documents for public access,
     * applying optional HTTP queries (filters, sorts, pagination).
     *
     * @param  array<string, mixed>  $queries
     * @return Collection<int, Document>|Paginator
     */
    public function getPublished(array $queries = []): Collection|Paginator
    {
        return $this->handleMaybePaginatedQuery(fn () => $this->buildQuery()->published(), $queries);
    }

    /**
     * Toggle the publication status of a specific document (draft <-> published).
     * Automatically updates the 'published_at' timestamp accordingly.
     */
    public function togglePublish(Document|string $document): Document
    {
        /** @var Document $doc */
        $doc = $this->ensureModel($document);

        $isPublished = $doc->status === DocumentStatus::PUBLISHED;

        /** @var Document $updatedDoc */
        $updatedDoc = $this->update($doc, [
            'status' => $isPublished
                ? DocumentStatus::DRAFT->value
                : DocumentStatus::PUBLISHED->value,
            'published_at' => $isPublished ? null : now(),
        ]);

        return $updatedDoc;
    }
}
