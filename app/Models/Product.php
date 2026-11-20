<?php
namespace App\Models;

use Core\Model;

class Product extends Model
{
    protected static string $table = 'products';
    protected static array $fillable = ['category_id', 'name', 'description', 'price', 'stock', 'image_url', 'is_featured'];

    private const SELECT = 'SELECT p.*, c.name AS category_name, c.icon AS category_icon
                            FROM products p JOIN categories c ON c.id = p.category_id';

    public static function findWithCategory($id): ?array
    {
        return static::db()->fetch(self::SELECT . ' WHERE p.id = ? LIMIT 1', [$id]);
    }

    public static function search(?string $term, ?int $categoryId, int $page, int $perPage): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($term !== null && $term !== '') {
            $like     = '%' . addcslashes($term, '%_\\') . '%';
            $where[]  = '(p.name LIKE ? OR p.description LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }
        if ($categoryId) {
            $where[]  = 'p.category_id = ?';
            $params[] = $categoryId;
        }
        $whereSql = implode(' AND ', $where);

        $total  = (int) static::db()->value("SELECT COUNT(*) FROM products p WHERE $whereSql", $params);
        $pages  = max(1, (int) ceil($total / $perPage));
        $page   = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $rows = static::db()->fetchAll(
            self::SELECT . " WHERE $whereSql ORDER BY p.id DESC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function featured(int $limit = 4): array
    {
        return static::db()->fetchAll(
            self::SELECT . ' WHERE p.is_featured = 1 AND p.stock > 0 ORDER BY p.id DESC LIMIT ' . (int) $limit
        );
    }

    public static function related(int $categoryId, int $exceptId, int $limit = 4): array
    {
        return static::db()->fetchAll(
            self::SELECT . ' WHERE p.category_id = ? AND p.id <> ? ORDER BY p.id DESC LIMIT ' . (int) $limit,
            [$categoryId, $exceptId]
        );
    }

    public static function adminList(): array
    {
        return static::db()->fetchAll(self::SELECT . ' ORDER BY p.id DESC');
    }

    public static function lowStock(int $threshold = 5): array
    {
        return static::db()->fetchAll(
            self::SELECT . ' WHERE p.stock <= ? ORDER BY p.stock ASC, p.name LIMIT 8',
            [$threshold]
        );
    }

    public static function byIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = static::db()->fetchAll(
            self::SELECT . " WHERE p.id IN ($placeholders)",
            array_values($ids)
        );
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['id']] = $row;
        }
        return $indexed;
    }
}
