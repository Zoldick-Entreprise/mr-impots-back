<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class AdminResource
 *
 * @property-read User $resource
 * Transforms the User model into a standardized JSON array for Admin API responses,
 * including roles and permissions.
 */
final class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array<string, mixed> The formatted admin data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'avatar' => $this->resource->avatar,
            'roles' => $this->whenLoaded('roles'),
            'permissions' => $this->whenLoaded('permissions'),
            'created_at' => $this->created_at,
        ];
    }
}
