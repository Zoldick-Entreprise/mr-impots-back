<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for a download record.
 *
 * @property Download $resource
 */
final class DownloadResource extends JsonResource
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
            'document_id' => $this->resource->document_id,
            'user_id' => $this->resource->user_id,
            'ip' => $this->resource->ip,
            'downloaded_at' => $this->resource->created_at,
        ];
    }
}
