<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreDocumentRequest
 *
 * Handles validation for creating a new Document and translatable title.
 */
final class StoreDocumentRequest extends FormRequest
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
            'title' => ['required', 'array'],
            'title.fr' => ['required', 'string', 'max:255'],
            'title.en' => ['required', 'string', 'max:255'],
            'description' => ['required', 'array'],
            'description.fr' => ['required', 'string', 'max:255'],
            'description.en' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file_fr.mimes' => 'Le fichier français doit être un document de type PDF (application/pdf).',
            'file_en.mimes' => 'Le fichier anglais doit être un document de type PDF (application/pdf).',
            'file_fr.max' => 'Le fichier français dépasse la taille maximale autorisée.',
            'file_en.max' => 'Le fichier anglais dépasse la taille maximale autorisée.',
        ];
    }
}
