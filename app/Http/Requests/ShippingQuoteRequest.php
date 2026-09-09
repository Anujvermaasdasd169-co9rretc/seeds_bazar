<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('cart'))) {
            $decoded = json_decode($this->input('cart'), true);
            $this->merge(['cart' => is_array($decoded) ? $decoded : []]);
        }
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'cart' => ['required', 'array', 'min:1', 'max:50'],
            'cart.*.id' => ['required', 'integer', 'distinct'],
            'cart.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
