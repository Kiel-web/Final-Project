<?php
$root = dirname(__DIR__);

$out  = "CREATE DATABASE IF NOT EXISTS whiskerhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$out .= "USE whiskerhub;\n";
$out .= "SET NAMES utf8mb4;\n\n";

$files = glob($root . '/database/migrations/*.sql');
sort($files);
foreach ($files as $file) {
    $out .= trim(file_get_contents($file)) . "\n\n";
}

$out .= trim(file_get_contents($root . '/database/seeds/seed.sql')) . "\n";

file_put_contents($root . '/database/whiskerhub_full.sql', $out);
echo "Written database/whiskerhub_full.sql (" . strlen($out) . " bytes)\n";
