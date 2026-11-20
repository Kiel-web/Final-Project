<section class="auth-box card card-pad">
    <h1>Log in</h1>
    <form method="post" action="<?= url('/login') ?>" class="form">
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
            <?= field_error('email') ?>
        </label>
        <label>Password
            <input type="password" name="password" required>
            <?= field_error('password') ?>
        </label>
        <button class="btn btn-large btn-block" type="submit">Log in</button>
    </form>
    <p class="muted center">No account yet? <a href="<?= url('/register') ?>">Sign up</a></p>
    <p class="muted small center">Demo: <code>customer@whiskerhub.test</code> / <code>customer123</code></p>
</section>
