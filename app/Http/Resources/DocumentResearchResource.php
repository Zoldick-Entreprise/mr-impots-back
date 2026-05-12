<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read DocumentPage $resource
 */
final class DocumentResearchResource extends JsonResource
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
            'page_number' => $this->resource->page_number,
            'content' => $this->resource->content,
            'document_id' => $this->resource->document_id,
            'locale' => $this->resource->locale,
            'document_title' => $this->resource->document->title,
        ];
    }
}
