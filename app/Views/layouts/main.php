<?php
use App\Services\CartService;
use Core\Auth;
use Core\Session;

$user    = Auth::user();
$message = Session::getFlash('message');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Home') ?> · <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= url('/') ?>">🐾 <?= e(config('app.name')) ?></a>

        <nav class="nav-links">
            <a href="<?= url('/shop') ?>">Shop</a>
            <a href="<?= url('/adopt') ?>">Adopt</a>
            <a href="<?= url('/breeds') ?>">Breed guide</a>
        </nav>

        <div class="nav-right">
            <a class="cart-link" href="<?= url('/cart') ?>">🛒 Cart
                <?php if (CartService::count() > 0): ?><span class="badge"><?= CartService::count() ?></span><?php endif; ?>
            </a>

            <?php if ($user): ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="btn btn-small btn-outline" href="<?= url('/admin') ?>">Admin</a>
                <?php endif; ?>
                <a href="<?= url('/orders') ?>">My orders</a>
                <form method="post" action="<?= url('/logout') ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <button class="link-button" type="submit">Log out (<?= e($user['name']) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= url('/login') ?>">Log in</a>
                <a class="btn btn-small" href="<?= url('/register') ?>">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?= e($message['type']) ?>"><?= e($message['text']) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        <p>🐱 <?= e(config('app.name')) ?> - IPT final project · built with a custom PHP MVC framework · cat photos &amp; breed data by
            <a href="https://thecatapi.com" target="_blank" rel="noopener">TheCatAPI</a></p>
    </div>
</footer>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
