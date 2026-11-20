<?php
namespace App\Services;

use App\Models\Product;
use Core\Session;

class CartService
{
    private const KEY = 'cart';

    public static function raw(): array
    {
        return Session::get(self::KEY, []);
    }

    public static function count(): int
    {
        return (int) array_sum(self::raw());
    }

    public static function add(int $productId, int $quantity): ?string
    {
        $product = Product::find($productId);
        if ($product === null) {
            return 'That product does not exist.';
        }
        if ((int) $product['stock'] <= 0) {
            return '"' . $product['name'] . '" is out of stock.';
        }

        $cart    = self::raw();
        $wanted  = ($cart[$productId] ?? 0) + max(1, $quantity);
        $allowed = min($wanted, (int) $product['stock']);

        $cart[$productId] = $allowed;
        Session::set(self::KEY, $cart);

        return $allowed < $wanted ? 'Only ' . $product['stock'] . ' of "' . $product['name'] . '" in stock - quantity adjusted.' : null;
    }

    public static function setQuantity(int $productId, int $quantity): ?string
    {
        if ($quantity <= 0) {
            self::remove($productId);
            return null;
        }

        $product = Product::find($productId);
        if ($product === null) {
            self::remove($productId);
            return 'That product no longer exists.';
        }

        $allowed = min($quantity, (int) $product['stock']);
        $cart = self::raw();
        $cart[$productId] = $allowed;
        Session::set(self::KEY, $cart);

        return $allowed < $quantity ? 'Only ' . $product['stock'] . ' in stock - quantity adjusted.' : null;
    }

    public static function remove(int $productId): void
    {
        $cart = self::raw();
        unset($cart[$productId]);
        Session::set(self::KEY, $cart);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function items(): array
    {
        $cart     = self::raw();
        $products = Product::byIds(array_keys($cart));
        $items    = [];

        foreach ($cart as $id => $quantity) {
            if (!isset($products[(int) $id])) {
                self::remove((int) $id);
                continue;
            }
            $product = $products[(int) $id];
            $items[] = [
                'product'  => $product,
                'quantity' => (int) $quantity,
                'subtotal' => round($product['price'] * $quantity, 2),
            ];
        }
        return $items;
    }

    public static function total(array $items): float
    {
        return round(array_sum(array_column($items, 'subtotal')), 2);
    }
}
