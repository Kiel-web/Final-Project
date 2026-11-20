<?php
namespace Core;

use Core\Middleware\CsrfMiddleware;
use Core\Middleware\LogMiddleware;

class Application
{
    private Router $router;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        Env::load($basePath . '/.env');
        Config::load($basePath . '/config');
        date_default_timezone_set((string) Config::get('app.timezone', 'UTC'));
        Database::init(Config::get('database', []));

        $debug = (bool) Config::get('app.debug', false);
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        $this->router = new Router();
        $this->router->globalMiddleware([LogMiddleware::class, CsrfMiddleware::class]);
        $registerRoutes = require $basePath . '/routes/web.php';
        $registerRoutes($this->router);
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        Session::start();
        $request = Request::capture();
        $this->handle($request)->send();
    }

    public function handle(Request $request): Response
    {
        Request::setCurrent($request);

        try {
            return $this->router->dispatch($request);
        } catch (HttpException $e) {
            return $this->errorResponse($request, $e->getStatusCode(), $e->getMessage());
        } catch (\PDOException $e) {
            Logger::error('Database error: ' . $e->getMessage(), ['path' => $request->path()]);
            $hint = Config::get('app.debug')
                ? 'Database problem: ' . $e->getMessage() . ' — is MySQL running in XAMPP and was database/whiskerhub_full.sql imported?'
                : 'We are having trouble with the database. Please try again later.';
            return $this->errorResponse($request, 500, $hint);
        } catch (\Throwable $e) {
            Logger::error(get_class($e) . ': ' . $e->getMessage(), [
                'file' => $e->getFile() . ':' . $e->getLine(),
                'path' => $request->path(),
            ]);
            $message = Config::get('app.debug') ? get_class($e) . ': ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')' : 'Something went wrong on our side.';
            return $this->errorResponse($request, 500, $message);
        }
    }

    private function errorResponse(Request $request, int $status, string $message): Response
    {
        $titles = [
            403 => 'Access denied',
            404 => 'Page not found',
            405 => 'Method not allowed',
            500 => 'Server error',
        ];
        $title = $titles[$status] ?? 'Error';

        if ($request->wantsJson()) {
            return Response::json(['ok' => false, 'error' => $message ?: $title], $status);
        }

        try {
            return Response::html(
                View::render('errors/error', ['status' => $status, 'title' => $title, 'message' => $message ?: $title]),
                $status
            );
        } catch (\Throwable $e) {
            return Response::html("<h1>$status - " . e($title) . '</h1><p>' . e($message) . '</p>', $status);
        }
    }
}
