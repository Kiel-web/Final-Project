<?php
namespace Core\Middleware;

use Core\Auth;
use Core\Logger;
use Core\Middleware;
use Core\Request;
use Core\Response;

class LogMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $start    = microtime(true);
        $response = $next($request);
        $ms       = (int) round((microtime(true) - $start) * 1000);

        Logger::info(sprintf(
            '%s %s -> %d (user %s, %dms)',
            $request->method(),
            $request->path(),
            $response->getStatusCode(),
            Auth::id() ?? 'guest',
            $ms
        ));

        return $response;
    }
}
