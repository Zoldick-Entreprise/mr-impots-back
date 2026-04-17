<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Repositories\Contracts\VideoRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class VideoController extends Controller
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

        $data = $request->validated();

        $video = $this->videoRepository->create($data);

        if ($request->hasFile('video')) {
            $video
                ->addMedia($request->file('video'))
                ->toMediaCollection('video');

            $video->unsetRelation('media');
        }

        return VideoResource::make($video)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $video = $this->videoRepository->whereFirstOrFail(['id' => $id]);

        $this->authorize('view', $video);

        return VideoResource::make($video)->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoRequest $request, string $id)
    {
        $video = $this->videoRepository->whereFirstOrFail(['id' => $id]);

        $this->authorize('update', $video);

        $data = $request->validated();

        $video = $this->videoRepository->update($id, $data);
        $video->unsetRelation('media');

        return VideoResource::make($video);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $video = $this->videoRepository->whereFirstOrFail(['id' => $id]);

        $this->authorize('delete', $video);

        $this->videoRepository->delete($id);

        return response()->json(null, 204);
    }
}
