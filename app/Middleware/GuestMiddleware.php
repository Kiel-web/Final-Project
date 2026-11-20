<?php
namespace App\Middleware;

use Core\Auth;
use Core\Middleware;
use Core\Request;
use Core\Response;

class GuestMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (Auth::check()) {
            return redirect('/');
        }

        return $next($request);
    }
}
