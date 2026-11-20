<?php
namespace Core;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $server;
    private array $params = [];

    private static string $basePath = '';

    private static ?Request $current = null;

    public function __construct(string $method, string $path, array $query = [], array $body = [], array $server = [])
    {
        $this->method = strtoupper($method);
        $this->path   = $path;
        $this->query  = $query;
        $this->body   = $body;
        $this->server = $server;
    }

    public static function capture(): self
    {
        $server = $_SERVER;
        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $body   = $_POST;

        if ($method === 'POST' && isset($body['_method'])) {
            $override = strtoupper((string) $body['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $uriPath = (string) parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uriPath = rawurldecode($uriPath);

        self::$basePath = self::detectBasePath($uriPath, $server);

        $path = $uriPath;
        if (self::$basePath !== '' && strpos($path, self::$basePath) === 0) {
            $path = substr($path, strlen(self::$basePath));
        }
        if (strpos($path, '/index.php') === 0) {
            $path = substr($path, strlen('/index.php'));
        }

        return new self($method, self::normalizePath($path), $_GET, $body, $server);
    }

    private static function detectBasePath(string $uriPath, array $server): string
    {
        $scriptDir = str_replace('\\', '/', dirname($server['SCRIPT_NAME'] ?? '/index.php'));
        $scriptDir = rtrim($scriptDir, '/');
        $parentDir = rtrim(str_replace('\\', '/', dirname($scriptDir)), '/');

        if ($scriptDir !== '' && self::startsWithSegment($uriPath, $scriptDir)) {
            return $scriptDir;
        }
        if ($parentDir !== '' && self::startsWithSegment($uriPath, $parentDir)) {
            return $parentDir;
        }
        return '';
    }

    private static function startsWithSegment(string $path, string $prefix): bool
    {
        return $path === $prefix || strpos($path, $prefix . '/') === 0;
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function current(): ?Request
    {
        return self::$current;
    }

    public static function setCurrent(Request $request): void
    {
        self::$current = $request;
    }

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = rtrim($basePath, '/');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function input(string $key, $default = null)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }
        return $this->query[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body) || array_key_exists($key, $this->query);
    }

    public function header(string $name, $default = null)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function wantsJson(): bool
    {
        return strpos($this->path, '/api/') === 0
            || strpos((string) $this->header('Accept', ''), 'application/json') !== false;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }
}
