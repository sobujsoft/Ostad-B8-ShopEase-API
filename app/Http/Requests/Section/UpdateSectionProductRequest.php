<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
