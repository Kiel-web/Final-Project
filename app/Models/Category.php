<?php
namespace App\Models;

use Core\Model;

class Category extends Model
{
    protected static string $table = 'categories';
    protected static array $fillable = ['name', 'icon'];

    public static function withProductCounts(): array
    {
        return static::db()->fetchAll(
            'SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id ORDER BY c.id'
        );
    }
}
