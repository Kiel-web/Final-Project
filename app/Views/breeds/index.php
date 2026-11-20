<h1>Cat breed guide</h1>
<p class="muted">Live data from TheCatAPI - saved in our database for 24 hours so the page stays fast.</p>

<form class="filter-bar" method="get" action="<?= url('/breeds') ?>">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Search by breed, origin or temperament…">
    <button class="btn" type="submit">Search</button>
    <?php if ($term !== ''): ?><a href="<?= url('/breeds') ?>" class="muted">Clear</a><?php endif; ?>
</form>

<?php if ($apiProblem): ?>
    <div class="alert alert-error">
        The breed guide could not be loaded: <?= e($apiProblem) ?><br>
        <small>Tip: add your free API key as <code>CAT_API_KEY</code> in the <code>.env</code> file, then reload this page.</small>
    </div>
<?php elseif (empty($breeds)): ?>
    <div class="empty"><p>😿 No breeds match "<?= e($term) ?>".</p></div>
<?php else: ?>
    <p class="muted"><?= count($breeds) ?> of <?= (int) $total ?> breeds</p>
    <div class="grid">
        <?php foreach ($breeds as $b): ?>
            <?php
            $photo = $b['image']['url'] ?? (!empty($b['reference_image_id']) ? 'https://cdn2.thecatapi.com/images/' . $b['reference_image_id'] . '.jpg' : null);
            ?>
            <article class="card breed-card">
                <?php if ($photo): ?>
                    <img class="pet-photo" src="<?= e($photo) ?>" alt="<?= e($b['name']) ?>" loading="lazy" onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="card-body">
                    <h3><?= e($b['name']) ?></h3>
                    <p class="muted small"><?= e($b['origin'] ?? '') ?> · <?= e($b['life_span'] ?? '?') ?> years</p>
                    <p class="small"><?= e($b['temperament'] ?? '') ?></p>
                    <?php if (isset($b['affection_level'])): ?>
                        <p class="small">Affection <?= rating_dots($b['affection_level']) ?><br>
                        Energy <?= rating_dots($b['energy_level'] ?? 0) ?></p>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
