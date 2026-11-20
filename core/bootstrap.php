<?php
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Core\\' => BASE_PATH . '/core/',
        'App\\'  => BASE_PATH . '/app/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (strpos($class, $prefix) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $directory . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

require __DIR__ . '/helpers.php';
