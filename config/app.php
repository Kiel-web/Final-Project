<?php
use Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'WhiskerHub'),
    'env'      => Env::get('APP_ENV', 'local'),
    'debug'    => Env::get('APP_DEBUG', true),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
    'currency' => Env::get('APP_CURRENCY', '₱'),
    'per_page' => 9,
];
