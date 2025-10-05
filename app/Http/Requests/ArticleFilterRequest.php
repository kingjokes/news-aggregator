<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'category' => 'nullable|string',
            'source' => 'nullable|string',
            'author' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}