<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class CartService
{
    public const COOKIE = 'sp_cart_token';

    public function current(?Request $request = null): Cart
    {
        $request ??= request();
        $user = $request->user();

        if ($user) {
            return Cart::query()->firstOrCreate(['user_id' => $user->id])->load(['items.product.category']);
        }

        $token = (string) $request->cookie(self::COOKIE);
        if ($token === '') {
            $token = Cart::newGuestToken();
            Cookie::queue($this->tokenCookie($token));
        }

        return Cart::query()->firstOrCreate(['guest_token' => $token])->load(['items.product.category']);
    }

    public function tokenCookie(string $token): SymfonyCookie
    {
        return cookie(self::COOKIE, $token, 60 * 24 * 45, '/', null, (bool) config('session.secure'), true, false, 'lax');
    }

    public function add(Cart $cart, int $productId, int $quantity = 1): Cart
    {
        $quantity = max(1, $quantity);
        $product = $this->sellable($productId);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $next = min((int) $item->quantity + $quantity, max(1, (int) $product->stock_quantity));
        $item->quantity = $next;
        $item->save();

        return $cart->fresh(['items.product.category']);
    }

    public function setQuantity(Cart $cart, int $productId, int $quantity): Cart
    {
        if ($quantity < 1) {
            $cart->items()->where('product_id', $productId)->delete();

            return $cart->fresh(['items.product.category']);
        }

        $product = $this->sellable($productId);
        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => min($quantity, max(1, (int) $product->stock_quantity))],
        );

        return $cart->fresh(['items.product.category']);
    }

    public function remove(Cart $cart, int $productId): Cart
    {
        $cart->items()->where('product_id', $productId)->delete();

        return $cart->fresh(['items.product.category']);
    }

    public function clear(Cart $cart): Cart
    {
        $cart->items()->delete();

        return $cart->fresh(['items.product.category']);
    }

    /**
     * @param  list<array{id:int,quantity:int}>  $lines
     */
    public function sync(Cart $cart, array $lines): Cart
    {
        foreach ($lines as $line) {
            $id = (int) ($line['id'] ?? 0);
            $qty = (int) ($line['quantity'] ?? 0);
            if ($id < 1 || $qty < 1) {
                continue;
            }
            try {
                $this->add($cart, $id, $qty);
            } catch (\Throwable) {
                continue;
            }
        }

        return $cart->fresh(['items.product.category']);
    }

    public function mergeGuestIntoUser(User $user, ?string $guestToken): void
    {
        if (! filled($guestToken)) {
            return;
        }

        $guest = Cart::query()->where('guest_token', $guestToken)->first();
        if (! $guest) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
        foreach ($guest->items as $item) {
            try {
                $this->add($userCart, (int) $item->product_id, (int) $item->quantity);
            } catch (\Throwable) {
                continue;
            }
        }
        $guest->items()->delete();
        $guest->delete();
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    /** @return Collection<int, array{id:int,quantity:int}> */
    public function lines(Cart $cart): Collection
    {
        return $cart->items
            ->filter(fn (CartItem $item) => $item->product?->is_active && $item->product->stock_quantity > 0)
            ->map(fn (CartItem $item) => [
                'id' => (int) $item->product_id,
                'quantity' => min((int) $item->quantity, (int) $item->product->stock_quantity),
            ])
            ->values();
    }

    /** @return list<array<string, mixed>> */
    public function payload(Cart $cart): array
    {
        return $cart->items
            ->filter(fn (CartItem $item) => $item->product !== null)
            ->map(function (CartItem $item): array {
                $product = $item->product;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'mrp' => $product->mrp !== null ? (float) $product->mrp : null,
                    'unit' => $product->unit,
                    'emoji' => $product->emoji,
                    'image' => $product->image_url,
                    'quantity' => (int) $item->quantity,
                    'stock_quantity' => (int) $product->stock_quantity,
                    'in_stock' => $product->is_active && $product->stock_quantity > 0,
                    'url' => route('products.show', $product),
                ];
            })
            ->values()
            ->all();
    }

    private function sellable(int $productId): Product
    {
        $product = Product::query()->whereKey($productId)->where('is_active', true)->first();
        if (! $product || $product->stock_quantity < 1) {
            abort(422, 'This product is not available.');
        }

        return $product;
    }
}
