<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->carts->current($request);

        return response()->json(['items' => $this->carts->payload($cart)]);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'max:50'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = $this->carts->sync($this->carts->current($request), $data['items']);

        return response()->json(['items' => $this->carts->payload($cart)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $cart = $this->carts->add($this->carts->current($request), (int) $data['product_id'], (int) ($data['quantity'] ?? 1));

        return response()->json(['items' => $this->carts->payload($cart)]);
    }

    public function update(Request $request, int $product): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $cart = $this->carts->setQuantity($this->carts->current($request), $product, (int) $data['quantity']);

        return response()->json(['items' => $this->carts->payload($cart)]);
    }

    public function destroyItem(Request $request, int $product): JsonResponse
    {
        $cart = $this->carts->remove($this->carts->current($request), $product);

        return response()->json(['items' => $this->carts->payload($cart)]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $cart = $this->carts->clear($this->carts->current($request));

        return response()->json(['items' => $this->carts->payload($cart)]);
    }
}
