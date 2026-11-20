<section class="hero">
    <div class="hero-text">
        <h1>Everything your cat needs, and a cat that needs you.</h1>
        <p>Shop quality food, toys and beds - or give a loving cat a forever home through our adoption list.</p>
        <p>
            <a class="btn btn-large" href="<?= url('/shop') ?>">Shop now</a>
            <a class="btn btn-large btn-outline" href="<?= url('/adopt') ?>">Adopt a cat</a>
        </p>
    </div>

    <aside class="cat-of-day" id="cat-of-the-day" data-endpoint="<?= url('/api/cat-of-the-day') ?>">
        <h3>😺 Cat of the day</h3>
        <div class="cat-of-day-photo"><span>🐱</span></div>
        <p class="muted small cat-of-day-caption">Loading today's cat…</p>
    </aside>
</section>

<section>
    <div class="chips">
        <?php foreach ($categories as $c): ?>
            <a class="chip" href="<?= url('/shop', ['category' => $c['id']]) ?>"><?= e($c['icon']) ?> <?= e($c['name']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2>Featured products</h2>
        <a href="<?= url('/shop') ?>">See all →</a>
    </div>
    <div class="grid">
        <?php foreach ($featured as $p): ?>
            <?= partial('partials/product_card', ['p' => $p]) ?>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-head">
        <h2>Cats looking for a home</h2>
        <a href="<?= url('/adopt') ?>">Meet them all →</a>
    </div>
    <div class="grid">
        <?php foreach ($pets as $pet): ?>
            <?= partial('partials/pet_card', ['pet' => $pet]) ?>
        <?php endforeach; ?>
    </div>
</section>
