<?php
namespace Core;

class Config
{
    private static array $items = [];

    public static function load(string $directory): void
    {
        foreach (glob(rtrim($directory, '/') . '/*.php') as $file) {
            self::$items[basename($file, '.php')] = require $file;
        }
    }

    public static function get(string $key, $default = null)
    {
        $value = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public static function set(string $key, $value): void
    {
        $ref = &self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
    }
}
