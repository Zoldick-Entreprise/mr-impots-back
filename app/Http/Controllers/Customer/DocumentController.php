<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentPageResource;
use App\Http\Resources\DocumentResource;
use App\Models\Download;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DocumentController
 *
 * Handles public-facing API operations for Documents.
 * Exposes only published documents to end-users or customers.
 */
final class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentRepository $repository,
    ) {}

    /**
     * Display a paginated listing of published documents with optional filters.
     *
     * @param  Request  $request  The incoming HTTP request containing optional filters.
     * @return ResourceCollection The paginated collection of documents.
     */
    public function index(Request $request): ResourceCollection
    {
        $queries = array_merge($request->query(), [
            'paginate' => $request->query('per_page', 20),
        ]);

        $documents = $this->repository->getPublished($queries);

        return DocumentResource::collection($documents);
    }

    /**
     * Display the specified published document with lazy-loaded paginated pages.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @param  string  $id  The ID of the document.
     * @return DocumentResource The formatted document resource.
     */
    public function show(string $id): DocumentResource
    {
        $document = $this->repository->retrieve($id);

        return DocumentResource::make($document);
    }

    /**
     * Display the paginated pages of a specified published document.
     *
     * @param  Request  $request  The incoming HTTP request containing optional filters.
     * @param  string  $id  The ID of the document.
     * @return JsonResponse The paginated collection of document pages.
     */
    public function getPages(Request $request, string $id): JsonResponse
    {
        $document = $this->repository->retrieve($id);

        if (! $document) {
            return response()->json([
                'message' => 'Document not found',
            ], 404);
        }

        $pages = $document
            ->pages()
            ->where('locale', app()->getLocale())
            ->orderBy('page_number')
            ->paginate((int) $request->query('per_page', 10));

        return DocumentPageResource::collection($pages)
            ->response();
    }

    /**
     * Download the specified published document.
     *
     * @param  string  $id  The ID of the document.
     * @return JsonResponse|StreamedResponse The download response.
     */
    public function download(string $id): JsonResponse|StreamedResponse
    {
        $document = $this->repository->retrieve($id);

        if (! $document || ! $document->isPublished()) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        Download::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(), // null pour les visiteurs
            'ip' => request()->ip(),
        ]);

        $file = $document->documentFrom(app()->getLocale());

        return response()->streamDownload(
            function () use ($file) {
                $stream = $file->stream();
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            $file->file_name ?? 'document.pdf',
            [
                'Content-Type' => 'application/pdf',
            ],
        );
    }
}
