<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentPageResource;
use App\Http\Resources\DocumentResource;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}
