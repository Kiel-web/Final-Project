<?php
namespace Core;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];

    protected static function db(): Database
    {
        return Database::getInstance();
    }

    public static function all(string $orderBy = 'id DESC'): array
    {
        return self::db()->fetchAll('SELECT * FROM ' . static::$table . ' ORDER BY ' . $orderBy);
    }

    public static function find($id): ?array
    {
        return self::db()->fetch(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1',
            [$id]
        );
    }

    public static function findOrFail($id): array
    {
        $row = static::find($id);
        if ($row === null) {
            throw new HttpException(404, 'Record not found');
        }
        return $row;
    }

    public static function findBy(string $column, $value): ?array
    {
        return self::db()->fetch(
            'SELECT * FROM ' . static::$table . ' WHERE ' . $column . ' = ? LIMIT 1',
            [$value]
        );
    }

    public static function where(string $column, $value, string $orderBy = 'id DESC'): array
    {
        return self::db()->fetchAll(
            'SELECT * FROM ' . static::$table . ' WHERE ' . $column . ' = ? ORDER BY ' . $orderBy,
            [$value]
        );
    }

    public static function create(array $data): int
    {
        $data = static::onlyFillable($data);
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        self::db()->execute(
            'INSERT INTO ' . static::$table . " ($columns) VALUES ($placeholders)",
            array_values($data)
        );
        return self::db()->lastInsertId();
    }

    public static function update($id, array $data): int
    {
        $data = static::onlyFillable($data);
        if (empty($data)) {
            return 0;
        }
        $assignments = implode(', ', array_map(function ($column) {
            return $column . ' = ?';
        }, array_keys($data)));

        $params   = array_values($data);
        $params[] = $id;

        return self::db()->execute(
            'UPDATE ' . static::$table . " SET $assignments WHERE " . static::$primaryKey . ' = ?',
            $params
        );
    }

    public static function delete($id): int
    {
        return self::db()->execute(
            'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?',
            [$id]
        );
    }

    public static function count(string $where = '1=1', array $params = []): int
    {
        return (int) self::db()->value('SELECT COUNT(*) FROM ' . static::$table . ' WHERE ' . $where, $params);
    }

    public static function paginate(int $page, int $perPage, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array
    {
        $total = static::count($where, $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $rows = self::db()->fetchAll(
            'SELECT * FROM ' . static::$table . " WHERE $where ORDER BY $orderBy LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    private static function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip(static::$fillable));
    }
}
