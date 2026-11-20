<?php
namespace App\Middleware;

use Core\Auth;
use Core\HttpException;
use Core\Middleware;
use Core\Request;
use Core\Response;

class AdminMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::isAdmin()) {
            throw new HttpException(403, 'You do not have permission to view this page.');
        }

        return $next($request);
    }
}
