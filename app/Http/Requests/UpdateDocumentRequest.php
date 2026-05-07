<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateDocumentRequest
 *
 * Handles validation for updating an existing Document's metadata (PATCH).
 * File uploads (PDFs) are not handled in this request to keep metadata
 * updates lightweight and separated from binary operations.
 */
final class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled via policies and middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'array'],
            'title.fr' => ['sometimes', 'string', 'max:255'],
            'title.en' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'array'],
            'description.fr' => ['sometimes', 'string', 'max:255'],
            'description.en' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'uuid', 'exists:categories,id'],
        ];
    }
}
