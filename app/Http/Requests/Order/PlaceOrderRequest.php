<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:255'],
            'customer_email'   => ['required', 'string', 'email', 'max:255'],
            'customer_phone'   => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method'   => ['required', 'string', 'in:cod,sslcommerz'],
        ];
    }
}
