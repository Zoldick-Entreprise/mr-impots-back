<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentResource;
use App\Repositories\Contracts\DocumentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
}
