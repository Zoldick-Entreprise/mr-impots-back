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
 * @mixin Document
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
            'id' => $this->id,
            'title' => $this->title, // Returns the JSON array containing 'fr' and 'en'
            'status' => $this->status,
            'ocr_status' => $this->ocr_status,
            'document_views' => $this->document_views,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships (Loaded only if eager loaded to prevent N+1)
            'category' => new CategoryResource($this->whenLoaded('category')),
            'uploaded_by' => new UserResource($this->whenLoaded('uploadedBy')),

            // Media Files (Using Spatie Media Library)
            // Assumes media is stored on a cloud disk like R2, returning the absolute URL
            'files' => [
                'fr' => $this->getFirstMediaUrl('document_fr') ?: null,
                'en' => $this->getFirstMediaUrl('document_en') ?: null,
            ],
        ];
    }
}
