<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignSectionProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => ['required', 'array', 'min:1'],
            'product_id.*' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
                Rule::unique('section_products', 'product_id'),
            ],
            'sort_order'   => ['nullable', 'array'],
            'sort_order.*' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'      => 'At least one product ID is required.',
            'product_id.array'         => 'product_id must be an array.',
            'product_id.*.integer'     => 'Each product ID must be an integer.',
            'product_id.*.distinct'    => 'Duplicate product IDs are not allowed in the same request.',
            'product_id.*.exists'      => 'Product with ID :input does not exist.',
            'product_id.*.unique'      => 'Product with ID :input is already assigned to a section.',
            'sort_order.array'         => 'sort_order must be an array.',
            'sort_order.*.integer'     => 'Each sort order value must be an integer.',
            'sort_order.*.min'         => 'Each sort order value must be at least 0.',
        ];
    }
}
