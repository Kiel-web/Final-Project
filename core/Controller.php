<?php
namespace Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/main', int $status = 200): Response
    {
        return Response::html(View::render($view, $data, $layout), $status);
    }

    protected function json($data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $path): Response
    {
        return Response::redirect(url($path));
    }

    protected function back(Request $request, string $fallback = '/'): Response
    {
        $referer = (string) $request->header('Referer', '');
        $host    = (string) $request->header('Host', '');
        if ($referer !== '' && $host !== '' && parse_url($referer, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
            return Response::redirect($referer);
        }
        return $this->redirect($fallback);
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash('message', ['type' => $type, 'text' => $message]);
    }

    protected function backWithErrors(Request $request, array $errors, string $fallback = '/'): Response
    {
        Session::flash('errors', $errors);
        Session::flash('old', array_diff_key($request->all(), array_flip(['password', 'password_confirmation', '_token'])));
        return $this->back($request, $fallback);
    }

    protected function abort(int $status, string $message = ''): void
    {
        throw new HttpException($status, $message);
    }
}
