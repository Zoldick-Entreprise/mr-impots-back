<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Repositories\CommonRepository;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

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

    /**
     * Repository configuration.
     *
     * @var array<string, mixed>
     */
    protected array $config = [
        'includes' => ['category', 'uploadedBy'],
        'relations' => ['category', 'uploadedBy'],
        'sorts' => ['created_at', 'published_at'],
    ];

    public function __construct(array $config = [])
    {
        $this->config['filters'] = [
            'status',
            'category_id',
            AllowedFilter::scope('language'),
        ];

        parent::__construct($config);
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
        return $this->handleMaybePaginatedQuery(
            fn () => $this->buildQuery()->where(
                'status',
                DocumentStatus::Published->value,
            ),
            $queries,
        );
    }

    /**
     * Toggle the publication status of a specific document (draft <-> published).
     * Automatically updates the 'published_at' timestamp accordingly.
     */
    public function togglePublish(Document|string $document): Document
    {
        /** @var Document $doc */
        $doc = $this->ensureModel($document);

        $isPublished = $doc->status === DocumentStatus::Published;

        /** @var Document $updatedDoc */
        $updatedDoc = $this->update($doc, [
            'status' => $isPublished
                ? DocumentStatus::Draft->value
                : DocumentStatus::Published->value,
            'published_at' => $isPublished ? null : now(),
        ]);

        return $updatedDoc;
    }
}
