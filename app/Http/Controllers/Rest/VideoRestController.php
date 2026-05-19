<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Requests\UploadVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Repositories\Contracts\VideoRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;

final class VideoRestController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly VideoRepository $videoRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): ResourceCollection
    {
        $this->authorize('viewAny', Video::class);

        return VideoResource::collection($this->videoRepository->all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoRequest $request)
    {
        $this->authorize('create', Video::class);

        $video = $this->videoRepository->create($request->validated());

        return VideoResource::make($video)->response()->setStatusCode(201);
    }

    /**
     * Upload video for the specified video model.
     */
    public function upload(UploadVideoRequest $request, Video $video): JsonResponse
    {
        $this->authorize('update', $video);

        if ($request->hasFile('video')) {
            $video
                ->addMedia($request->file('video'))
                ->toMediaCollection('video');

            Cache::forget("thumbnail_url_{$video->id}");
            Cache::forget("video_url_{$video->id}");
        }

        return VideoResource::make($video)->response()->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $video)
    {
        $model = $this->videoRepository->retrieve($video);

        $this->authorize('view', $model);

        return VideoResource::make($model)->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoRequest $request, string $video)
    {
        $model = $this->videoRepository->retrieve($video);

        $this->authorize('update', $model);

        $model = $this->videoRepository->update($model, $request->validated());

        return VideoResource::make($model)->response();
    }

    /**
     * Toggle the published status of the specified resource.
     */
    public function togglePublish(string $id)
    {
        $video = $this->videoRepository->retrieve($id);

        $this->authorize('update', $video);

        $video = $this->videoRepository->update($video, [
            'published' => ! $video->published,
            'published_at' => ! $video->published ? now() : null,
        ]);

        return VideoResource::make($video)->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);

        $this->videoRepository->delete($video);

        return $this->successResponse(status: 204);
    }
}
