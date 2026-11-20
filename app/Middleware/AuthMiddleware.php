<?php
namespace App\Middleware;

use Core\Auth;
use Core\Middleware;
use Core\Request;
use Core\Response;
use Core\Session;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!Auth::check()) {
            if ($request->isMethod('GET')) {
                Session::set('intended', $request->path());
            }
            Session::flash('message', ['type' => 'info', 'text' => 'Please log in to continue.']);
            return redirect('/login');
        }

        return $next($request);
    }
}
