<h1>Shop</h1>

<form class="filter-bar" method="get" action="<?= url('/shop') ?>">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Search products…">
    <select name="category">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $categoryId === (int) $c['id'] ? 'selected' : '' ?>>
                <?= e($c['icon']) ?> <?= e($c['name']) ?> (<?= (int) $c['product_count'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Search</button>
    <?php if ($term !== '' || $categoryId): ?><a href="<?= url('/shop') ?>" class="muted">Clear</a><?php endif; ?>
</form>

<p class="muted"><?= (int) $paging['total'] ?> product<?= $paging['total'] === 1 ? '' : 's' ?> found</p>

<?php if (empty($products)): ?>
    <div class="empty">
        <p>😿 No products match your search.</p>
        <a class="btn" href="<?= url('/shop') ?>">Show all products</a>
    </div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($products as $p): ?>
            <?= partial('partials/product_card', ['p' => $p]) ?>
        <?php endforeach; ?>
    </div>
    <?= partial('partials/pagination', ['paging' => $paging, 'path' => '/shop', 'query' => ['q' => $term, 'category' => $categoryId]]) ?>
<?php endif; ?>
