<?php
use Core\Auth;
use Core\Config;
use Core\Env;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

function env(string $key, $default = null)
{
    return Env::get($key, $default);
}

function config(string $key, $default = null)
{
    return Config::get($key, $default);
}

function base_path(string $path = ''): string
{
    return BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '', array $query = []): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $url = Request::basePath() . '/' . ltrim($path, '/');
    if ($url === '/' || $path === '') {
        $url = Request::basePath() . '/';
    }
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    return $url;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): Response
{
    return Response::redirect(url($path));
}

function money($amount): string
{
    return config('app.currency', '₱') . number_format((float) $amount, 2);
}

function csrf_token(): string
{
    if (!Session::has('_csrf')) {
        Session::set('_csrf', bin2hex(random_bytes(32)));
    }
    return Session::get('_csrf');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
}

function old(string $key, $default = '')
{
    $old = Session::getFlash('old', []);
    return $old[$key] ?? $default;
}

function error_for(string $field): ?string
{
    $errors = Session::getFlash('errors', []);
    return $errors[$field] ?? null;
}

function auth_user(): ?array
{
    return Auth::user();
}

function partial(string $view, array $data = []): string
{
    return View::partial($view, $data);
}

function format_age($months): string
{
    $months = (int) $months;
    if ($months < 12) {
        return $months . ' mo';
    }
    $years = intdiv($months, 12);
    $rest  = $months % 12;
    return $years . ' yr' . ($rest ? ' ' . $rest . ' mo' : '');
}

function field_error(string $field): string
{
    $message = error_for($field);
    return $message ? '<span class="field-error">' . e($message) . '</span>' : '';
}

function rating_dots($value, int $max = 5): string
{
    $value = max(0, min($max, (int) $value));
    return '<span class="dots" title="' . $value . ' / ' . $max . '">' . str_repeat('●', $value) . str_repeat('○', $max - $value) . '</span>';
}

function current_path(): string
{
    $request = Request::current();
    return $request ? $request->path() : '/';
}
