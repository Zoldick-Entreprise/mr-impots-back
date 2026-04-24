<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'array'],
            'title.*' => ['required', 'string'],
            'description' => ['required', 'array'],
            'description.*' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'published_at' => ['required', 'date'],
            'video' => ['required', 'file', 'mimes:mp4,mov,avi,wmv', 'max:204800'],
        ];
    }
}
