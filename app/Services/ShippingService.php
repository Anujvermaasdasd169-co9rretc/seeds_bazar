<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Setting;
use Illuminate\Support\Collection;

class ShippingService
{
    /** @return array{subtotal: float, shipping: float, total: float, method: string, estimate: string} */
    public function quote(Collection $products, Collection $cart, Address $address): array
    {
        $subtotal = 0.0;
        foreach ($cart as $line) {
            $product = $products->get((int) $line['id']);
            $subtotal += (float) $product->price * (int) $line['quantity'];
        }

        $threshold = (float) Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold'));
        $flatRate = (float) Setting::get('shipping_flat_rate', (string) config('seeds_bazar.shipping.flat_rate'));
        $shipping = $subtotal >= $threshold ? 0.0 : $flatRate;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping' => round($shipping, 2),
            'total' => round($subtotal + $shipping, 2),
            'method' => (string) Setting::get('shipping_method', config('seeds_bazar.shipping.method')),
            'estimate' => (string) Setting::get('shipping_estimate', config('seeds_bazar.shipping.estimate')),
        ];
    }

    public function serviceable(Address $address): bool
    {
        $configured = trim((string) config('seeds_bazar.shipping.serviceable_pincodes'));
        if ($configured === '') {
            return true;
        }

        return in_array($address->postal_code, array_filter(array_map('trim', explode(',', $configured))), true);
    }
}
