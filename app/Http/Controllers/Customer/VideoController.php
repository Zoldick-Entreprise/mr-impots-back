<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Repositories\Contracts\VideoRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class VideoController
 *
 * Handles video retrieval for the customer-facing API.
 * Restricts access to only published videos and provides optimized, read-only endpoints.
 */
final class VideoController extends Controller
{
    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {}

    /**
     * Display a paginated listing of published videos.
     *
     * @param  Request  $request  The incoming HTTP request containing query parameters.
     */
    public function index(Request $request): ResourceCollection
    {
        $videos = $this->videoRepository
            ->published()
            ->paginate((int) $request->query('paginate', 15))
            ->appends($request->query());

        return VideoResource::collection($videos);
    }

    /**
     * Display the specified published video.
     *
     * @param  string  $id  The UUID of the video.
     *
     * @throws ModelNotFoundException If the video does not exist or is not published.
     */
    public function show(string $id): VideoResource
    {
        // Enforce the published scope even on direct retrieval
        $video = $this->videoRepository->published()->findOrFail($id);

        return new VideoResource($video);
    }
}
