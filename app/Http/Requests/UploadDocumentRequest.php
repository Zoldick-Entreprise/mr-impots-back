<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UploadDocumentRequest
 *
 * Handles validation for uploading PDF files for a Document (FR/EN)
 */
final class UploadDocumentRequest extends FormRequest
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
        $maxSizeKb = (int) config('app.max_pdf_size_mb') * 1024;

        return [
            'file_fr' => ['file', 'mimes:pdf', 'max:'.$maxSizeKb],
            'file_en' => ['file', 'mimes:pdf', 'max:'.$maxSizeKb],
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
