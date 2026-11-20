<?php
use Core\Auth;
use Core\Session;

$user    = Auth::user();
$message = Session::getFlash('message');
$path    = current_path();

if (!function_exists('admin_active')) {
    function admin_active(string $current, string $prefix): string
    {
        if ($prefix === '/admin') {
            return $current === '/admin' ? 'active' : '';
        }
        return strpos($current, $prefix) === 0 ? 'active' : '';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Admin') ?> · Admin · <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="admin-body">

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <a class="brand" href="<?= url('/admin') ?>">🐾 Admin</a>
        <nav>
            <a class="<?= admin_active($path, '/admin') ?>" href="<?= url('/admin') ?>">📊 Dashboard</a>
            <a class="<?= admin_active($path, '/admin/products') ?>" href="<?= url('/admin/products') ?>">🛍️ Products</a>
            <a class="<?= admin_active($path, '/admin/pets') ?>" href="<?= url('/admin/pets') ?>">🐱 Adoption listings</a>
            <a class="<?= admin_active($path, '/admin/orders') ?>" href="<?= url('/admin/orders') ?>">📦 Orders</a>
            <a class="<?= admin_active($path, '/admin/inquiries') ?>" href="<?= url('/admin/inquiries') ?>">✉️ Inquiries</a>
        </nav>
        <div class="admin-sidebar-foot">
            <a href="<?= url('/') ?>">← View website</a>
            <form method="post" action="<?= url('/logout') ?>">
                <?= csrf_field() ?>
                <button class="link-button" type="submit">Log out (<?= e($user['name'] ?? '') ?>)</button>
            </form>
        </div>
    </aside>

    <section class="admin-main">
        <?php if ($message): ?>
            <div class="alert alert-<?= e($message['type']) ?>"><?= e($message['text']) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </section>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
