<?php
namespace Core\Middleware;

use Core\HttpException;
use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

class CsrfMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $sent     = (string) ($request->input('_token') ?? $request->header('X-CSRF-TOKEN', ''));
            $expected = (string) Session::get('_csrf', '');

            if ($expected === '' || !hash_equals($expected, $sent)) {
                throw new HttpException(403, 'Your session expired or the form was invalid. Please go back, reload the page and try again.');
            }
        }

        return $next($request);
    }
}
