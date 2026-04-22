<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rest;

use App\Enums\DocumentStatus;
use App\Enums\OcrStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Class DocumentRestController
 *
 * Handles administrative API operations for Documents (CRUD, publish toggle).
 */
final class DocumentRestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly DocumentRepository $repository,
    ) {}

    /**
     * Display a paginated listing of the documents with optional filters.
     */
    public function index(Request $request): ResourceCollection
    {
        $this->authorize('viewAny', Document::class);

        $queries = array_merge($request->query(), [
            'paginate' => $request->query('per_page', 20),
        ]);

        return DocumentResource::collection($this->repository->all($queries));
    }

    /**
     * Store a newly created document and process PDF uploads.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = DB::transaction(function () use ($request) {
            /** @var Document $doc */
            $doc = $this->repository->create([
                'title' => $request->validated('title'),
                'category_id' => $request->validated('category_id'),
                'status' => DocumentStatus::DRAFT->value,
                'ocr_status' => OcrStatus::PENDING->value,
                'uploaded_by' => $request->user()->id,
            ]);

            if ($request->hasFile('file_fr')) {
                $doc->addMediaFromRequest('file_fr')->toMediaCollection(
                    'document_fr',
                );
            }

            if ($request->hasFile('file_en')) {
                $doc->addMediaFromRequest('file_en')->toMediaCollection(
                    'document_en',
                );
            }

            return $doc;
        });

        return DocumentResource::make(
            $document->load(['uploadedBy']),
        )->response();
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->increment('document_views');

        return DocumentResource::make(
            $document->load(['uploadedBy']),
        )->response();
    }

    /**
     * Update the specified document's metadata only.
     */
    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $this->authorize('update', $document);

        /** @var Document $updatedDocument */
        $updatedDocument = $this->repository->update(
            $document,
            $request->validated(),
        );

        return DocumentResource::make(
            $updatedDocument->load(['uploadedBy']),
        )->response();
    }

    /**
     * Logically delete the document (archive) and remove files from R2.
     */
    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        DB::transaction(function () use ($document) {
            $this->repository->update($document, [
                'status' => DocumentStatus::ARCHIVED->value,
            ]);
            $document->clearMediaCollection('document_fr');
            $document->clearMediaCollection('document_en');
        });

        return response()->json(null, 204);
    }

    /**
     * Toggle the publish status of the document.
     */
    public function togglePublish(Request $request, Document $document): JsonResponse
    {
        $this->authorize('togglePublish', $document);

        $updatedDocument = $this->repository->togglePublish($document);

        return DocumentResource::make(
            $updatedDocument->load(['uploadedBy']),
        )->response();
    }
}
