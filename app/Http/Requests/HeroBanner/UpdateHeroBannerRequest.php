<?php

namespace App\Http\Requests\HeroBanner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banner_img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'badge_txt'  => ['nullable', 'string', 'max:100'],
            'title'      => ['sometimes', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:500'],
            'button_txt' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'is_active'  => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
