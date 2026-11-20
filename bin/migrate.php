<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/core/bootstrap.php';

use Core\Config;
use Core\Env;
use Core\Migrator;

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');

$db = Config::get('database');
$fresh = in_array('--fresh', $argv, true);
$seed  = $fresh || in_array('--seed', $argv, true);

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=%s', $db['host'], $db['port'], $db['charset']),
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to MySQL: " . $e->getMessage() . "\nIs MySQL started in the XAMPP Control Panel? Check DB_* values in .env\n");
    exit(1);
}

$name = str_replace('`', '', $db['database']);

if ($fresh) {
    $pdo->exec("DROP DATABASE IF EXISTS `$name`");
    echo "Dropped database $name\n";
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `$name`");
$pdo->exec('SET NAMES utf8mb4');

$migrator = new Migrator($pdo, BASE_PATH . '/database/migrations');
$applied  = $migrator->migrate();

foreach ($applied as $file) {
    echo "Migrated: $file\n";
}
if (empty($applied)) {
    echo "Nothing to migrate - database is up to date.\n";
}

if ($seed) {
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0 && !$fresh) {
        echo "Seed skipped: the users table already has data (use --fresh to reset).\n";
    } else {
        $migrator->runFile(BASE_PATH . '/database/seeds/seed.sql');
        echo "Seeded demo data.\n";
        echo "  Admin:    admin@whiskerhub.test / admin123\n";
        echo "  Customer: customer@whiskerhub.test / customer123\n";
    }
}

echo "Done.\n";
