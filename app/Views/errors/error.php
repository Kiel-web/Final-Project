<section class="error-page">
    <div class="error-code"><?= (int) $status ?></div>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <p><a class="btn" href="<?= url('/') ?>">Back to home</a></p>
</section>
