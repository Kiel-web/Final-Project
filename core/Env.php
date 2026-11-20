<?php
namespace Core;

class Env
{
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            $first = $value !== '' ? $value[0] : '';
            if (strlen($value) >= 2 && ($first === '"' || $first === "'") && substr($value, -1) === $first) {
                $value = substr($value, 1, -1);
            } else {
                $pos = strpos($value, ' #');
                if ($pos !== false) {
                    $value = rtrim(substr($value, 0, $pos));
                }
            }

            self::$vars[$key] = $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        $real = getenv($key);
        if ($real !== false) {
            $value = $real;
        } elseif (array_key_exists($key, self::$vars)) {
            $value = self::$vars[$key];
        } else {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':  return true;
            case 'false': return false;
            case 'null':  return null;
        }
        return $value;
    }

    public static function set(string $key, string $value): void
    {
        self::$vars[$key] = $value;
    }
}
