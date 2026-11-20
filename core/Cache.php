<?php
namespace Core;

class Cache
{
    public static function get(string $key)
    {
        $row = Database::getInstance()->fetch(
            'SELECT payload FROM cache WHERE cache_key = ? AND expires_at > ?',
            [$key, time()]
        );
        return $row ? json_decode($row['payload'], true) : null;
    }

    public static function stale(string $key)
    {
        $row = Database::getInstance()->fetch('SELECT payload FROM cache WHERE cache_key = ?', [$key]);
        return $row ? json_decode($row['payload'], true) : null;
    }

    public static function put(string $key, $value, int $ttlSeconds): void
    {
        Database::getInstance()->execute(
            'INSERT INTO cache (cache_key, payload, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE payload = VALUES(payload), expires_at = VALUES(expires_at)',
            [$key, json_encode($value), time() + $ttlSeconds]
        );
    }

    public static function forget(string $key): void
    {
        Database::getInstance()->execute('DELETE FROM cache WHERE cache_key = ?', [$key]);
    }
}
