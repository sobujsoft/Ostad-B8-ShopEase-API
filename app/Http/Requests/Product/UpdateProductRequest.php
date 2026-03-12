<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'category_id'      => ['sometimes', 'integer', 'exists:categories,id'],
            'name'             => ['sometimes', 'string', 'max:255'],
            'code'             => ['sometimes', 'string', 'max:50', "unique:products,code,{$productId}"],
            'color'            => ['nullable', 'string', 'max:100'],
            'size'             => ['nullable', 'string', 'max:100'],
            'short_description'=> ['nullable', 'string', 'max:500'],
            'description'      => ['nullable', 'string'],
            'price'            => ['sometimes', 'numeric', 'min:0'],
            'discount_price'   => ['nullable', 'numeric', 'min:0'],
            'stock_status'     => ['nullable', 'in:in_stock,out_of_stock'],
            'is_active'        => ['nullable', 'boolean'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
}
