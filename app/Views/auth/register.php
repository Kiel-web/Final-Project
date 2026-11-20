<section class="auth-box card card-pad">
    <h1>Create your account</h1>
    <form method="post" action="<?= url('/register') ?>" class="form">
        <?= csrf_field() ?>
        <label>Full name
            <input type="text" name="name" value="<?= e(old('name')) ?>" required autofocus>
            <?= field_error('name') ?>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e(old('email')) ?>" required>
            <?= field_error('email') ?>
        </label>
        <label>Password (min. 6 characters)
            <input type="password" name="password" required>
            <?= field_error('password') ?>
        </label>
        <label>Confirm password
            <input type="password" name="password_confirmation" required>
        </label>
        <button class="btn btn-large btn-block" type="submit">Create account</button>
    </form>
    <p class="muted center">Already registered? <a href="<?= url('/login') ?>">Log in</a></p>
</section>
