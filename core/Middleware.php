<?php
namespace Core;

interface Middleware
{
    public function handle(Request $request, callable $next): Response;
}
