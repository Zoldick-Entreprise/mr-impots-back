<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class VideoResource
 *
 * Transforms the Video model into a comprehensive JSON payload intended for the Rest (Admin) API.
 * This resource exposes all internal fields, raw timestamps, and relations necessary for administrative panels.
 *
 * @mixin Video
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
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title, // Translatable array
            'description' => $this->description, // Translatable array
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'is_featured' => $this->is_featured,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
