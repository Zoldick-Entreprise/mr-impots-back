<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'array'],
            'name.fr' => ['required', 'string'],
            'name.en' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:categories,slug'],
            'childrens' => ['nullable', 'array', 'unique_in_array:slug'],
            'childrens.*.slug' => ['required', 'string', 'unique:categories,slug'],
            'childrens.*.name' => ['required', 'array'],
            'childrens.*.name.fr' => ['required', 'string'],
            'childrens.*.name.en' => ['required', 'string'],
        ];
    }
}
