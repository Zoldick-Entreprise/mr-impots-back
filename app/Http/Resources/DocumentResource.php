<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DocumentResource
 *
 * Formats the Document model for API responses.
 * Includes media URLs for the French and English PDF versions if available,
 * and lazily evaluates relationships to avoid N+1 issues.
 *
 * @property Document $resource
 */
final class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'status' => $this->resource->status,
            'ocr_status' => $this->resource->ocr_status,
            'document_views' => $this->resource->document_views,
            'published_at' => $this->resource->published_at,
            'created_at' => $this->resource->created_at,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'uploaded_by' => new UserResource($this->whenLoaded('uploadedBy')),

            'files' => [
                'fr' => $this->resource->fr_document,
                'en' => $this->resource->en_document,
            ],
        ];
    }
}
