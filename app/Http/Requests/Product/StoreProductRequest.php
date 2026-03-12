<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'name'             => ['required', 'string', 'max:255'],
            'code'             => ['required', 'string', 'max:50', 'unique:products,code'],
            'color'            => ['nullable', 'string', 'max:100'],
            'size'             => ['nullable', 'string', 'max:100'],
            'short_description'=> ['nullable', 'string', 'max:500'],
            'description'      => ['nullable', 'string'],
            'price'            => ['required', 'numeric', 'min:0'],
            'discount_price'   => ['nullable', 'numeric', 'min:0'],
            'stock_status'     => ['nullable', 'in:in_stock,out_of_stock'],
            'is_active'        => ['nullable', 'boolean'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
