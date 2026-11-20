<?php
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/core/bootstrap.php';

$app = new Core\Application(BASE_PATH);
$app->run();
