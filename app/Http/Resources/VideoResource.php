<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class VideoResource
 *
 * @property Video $resource
 */
final class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array<string, mixed> The transformed video array payload.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'category_id' => $this->resource->category_id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'video_url' => $this->resource->video_url,
            'thumbnail_url' => $this->resource->thumbnail_url,
            'is_featured' => $this->resource->is_featured,
            'views_count' => $this->resource->views_count,
            'published_at' => $this->resource->published_at?->toIso8601String(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'is_favorited' => $this->when(
                $request->user() !== null,
                fn () => $this->resource->isFavoritedBy($request->user()),
            ),
        ];
    }
}
