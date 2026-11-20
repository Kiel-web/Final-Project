<?php
namespace App\Models;

use Core\Model;

class Order extends Model
{
    protected static string $table = 'orders';
    protected static array $fillable = [
        'user_id', 'total', 'status', 'payment_method', 'shipping_name', 'shipping_address', 'phone',
    ];

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];

    public static function place(int $userId, array $details, array $items): int
    {
        return static::db()->transaction(function ($db) use ($userId, $details, $items) {
            $total  = 0;
            $prices = [];

            foreach ($items as $item) {
                $locked = $db->fetch('SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE', [$item['product']['id']]);
                if ($locked === null || (int) $locked['stock'] < $item['quantity']) {
                    $name = $locked['name'] ?? $item['product']['name'];
                    throw new \RuntimeException("Sorry, \"$name\" does not have enough stock anymore.");
                }
                $prices[$locked['id']] = $locked['price'];
                $total += $locked['price'] * $item['quantity'];
            }

            $orderId = static::create(array_merge($details, [
                'user_id' => $userId,
                'total'   => round($total, 2),
                'status'  => 'pending',
            ]));

            foreach ($items as $item) {
                $product = $item['product'];
                OrderItem::create([
                    'order_id'     => $orderId,
                    'product_id'   => $product['id'],
                    'product_name' => $product['name'],
                    'unit_price'   => $prices[$product['id']],
                    'quantity'     => $item['quantity'],
                ]);
                $db->execute('UPDATE products SET stock = stock - ? WHERE id = ?', [$item['quantity'], $product['id']]);
            }

            return $orderId;
        });
    }

    public static function changeStatus(int $orderId, string $newStatus): void
    {
        if (!in_array($newStatus, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Unknown order status.');
        }

        static::db()->transaction(function ($db) use ($orderId, $newStatus) {
            $order = $db->fetch('SELECT id, status FROM orders WHERE id = ? FOR UPDATE', [$orderId]);
            if ($order === null) {
                throw new \RuntimeException('Order not found.');
            }
            if ($order['status'] === 'cancelled' && $newStatus !== 'cancelled') {
                throw new \RuntimeException('A cancelled order cannot be re-opened.');
            }
            if ($order['status'] !== 'cancelled' && $newStatus === 'cancelled') {
                $db->execute(
                    'UPDATE products p JOIN order_items i ON i.product_id = p.id
                     SET p.stock = p.stock + i.quantity WHERE i.order_id = ?',
                    [$orderId]
                );
            }
            $db->execute('UPDATE orders SET status = ? WHERE id = ?', [$newStatus, $orderId]);
        });
    }

    public static function forUser(int $userId): array
    {
        return static::db()->fetchAll(
            'SELECT o.*, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count
             FROM orders o WHERE o.user_id = ? ORDER BY o.id DESC',
            [$userId]
        );
    }

    public static function adminList(?string $status = null): array
    {
        $sql = 'SELECT o.*, u.name AS customer_name, u.email AS customer_email
                FROM orders o JOIN users u ON u.id = o.user_id';
        $params = [];
        if (in_array($status, self::STATUSES, true)) {
            $sql .= ' WHERE o.status = ?';
            $params[] = $status;
        }
        return static::db()->fetchAll($sql . ' ORDER BY o.id DESC', $params);
    }

    public static function withCustomer(int $id): ?array
    {
        return static::db()->fetch(
            'SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?',
            [$id]
        );
    }

    public static function countByStatus(): array
    {
        $rows = static::db()->fetchAll('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public static function revenue(): float
    {
        return (float) static::db()->value("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'cancelled'");
    }

    public static function recent(int $limit = 5): array
    {
        return static::db()->fetchAll(
            'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id
             ORDER BY o.id DESC LIMIT ' . (int) $limit
        );
    }
}
