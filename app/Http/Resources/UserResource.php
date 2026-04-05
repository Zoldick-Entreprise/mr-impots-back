<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 *
 * Transforms the User model into a standardized JSON array for API responses.
 * Ensures that sensitive information (like passwords) is stripped from the payload.
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array<string, mixed> The formatted user data.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'preferred_language' => $this->preferred_language,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
