<?php
namespace App\Models;

use Core\Model;

class OrderItem extends Model
{
    protected static string $table = 'order_items';
    protected static array $fillable = ['order_id', 'product_id', 'product_name', 'unit_price', 'quantity'];

    public static function forOrder(int $orderId): array
    {
        return static::where('order_id', $orderId, 'id ASC');
    }
}
