<?php
namespace Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::capture($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;
        return self::capture($layout, $data);
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::capture($view, $data);
    }

    private static function capture(string $view, array $data): string
    {
        $file = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $view");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
