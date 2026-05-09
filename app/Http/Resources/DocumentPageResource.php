<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\DocumentPage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for a document page.
 *
 * @property-read DocumentPage $resource
 */
final class DocumentPageResource extends JsonResource
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
        ];
    }
}
