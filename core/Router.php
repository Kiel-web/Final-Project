<?php
namespace Core;

class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    private array $groupStack = [];

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function globalMiddleware(array $middleware): void
    {
        $this->globalMiddleware = $middleware;
    }

    public function group(array $options, callable $callback): void
    {
        $this->groupStack[] = $options;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function add(string $method, string $path, $handler, array $middleware): void
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            $prefix     .= $group['prefix'] ?? '';
            $middleware  = array_merge($group['middleware'] ?? [], $middleware);
        }

        $fullPath = Request::normalizePath($prefix . $path);

        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'regex'      => $this->compile($fullPath),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    private function compile(string $path): string
    {
        $regex = preg_replace_callback('#\{(\w+)(?::([^}]+))?\}#', function ($m) {
            $pattern = $m[2] ?? '[^/]+';
            return '(?P<' . $m[1] . '>' . $pattern . ')';
        }, $path);

        return '#^' . $regex . '$#';
    }

    public function dispatch(Request $request): Response
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path(), $matches)) {
                continue;
            }

            if ($route['method'] !== $request->method()) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            $core = function (Request $req) use ($route, $params): Response {
                return $this->callHandler($route['handler'], $req, $params);
            };

            $stack = array_merge($this->globalMiddleware, $route['middleware']);
            return $this->runMiddleware($stack, $request, $core);
        }

        if (!empty($allowedMethods)) {
            throw new HttpException(405, 'Method not allowed');
        }
        throw new HttpException(404, 'Page not found');
    }

    private function runMiddleware(array $stack, Request $request, callable $core): Response
    {
        $next = $core;

        foreach (array_reverse($stack) as $middleware) {
            $next = function (Request $req) use ($middleware, $next): Response {
                $instance = is_string($middleware) ? new $middleware() : $middleware;
                return $instance->handle($req, $next);
            };
        }

        return $next($request);
    }

    private function callHandler($handler, Request $request, array $params): Response
    {
        $args = array_merge([$request], array_values($params));

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $result = call_user_func_array([new $class(), $method], $args);
        } else {
            $result = call_user_func_array($handler, $args);
        }

        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result)) {
            return Response::json($result);
        }
        return Response::html((string) $result);
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
