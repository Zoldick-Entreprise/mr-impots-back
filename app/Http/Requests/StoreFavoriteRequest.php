<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\Video;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreFavoriteRequest
 *
 * Request validation for storing a new favorite.
 */
class StoreFavoriteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication is handled by Sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'favoritable_type' => [
                'required',
                'string',
                Rule::in(['document', 'video']),
            ],
            'favoritable_id' => ['required', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('favoritable_type')) {
            $this->merge([
                'favoritable_type' => strtolower(
                    (string) $this->input('favoritable_type'),
                ),
            ]);
        }
    }

    /**
     * Get the resolved model class based on the type.
     */
    public function getFavoritableModelClass(): string
    {
        return match ($this->input('favoritable_type')) {
            'document' => Document::class,
            'video' => Video::class,
            default => throw new \InvalidArgumentException(
                'Invalid favoritable type',
            ),
        };
    }
}
