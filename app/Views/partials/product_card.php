<article class="card product-card">
    <a href="<?= url('/shop/' . $p['id']) ?>" class="thumb-link">
        <?php if (!empty($p['image_url'])): ?>
            <img class="thumb-img" src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php else: ?>
            <div class="thumb thumb-<?= (int) $p['category_id'] % 6 ?>"><span><?= e($p['category_icon']) ?></span></div>
        <?php endif; ?>
    </a>
    <div class="card-body">
        <span class="tag"><?= e($p['category_name']) ?></span>
        <h3><a href="<?= url('/shop/' . $p['id']) ?>"><?= e($p['name']) ?></a></h3>
        <div class="price-row">
            <strong class="price"><?= money($p['price']) ?></strong>
            <?php if ((int) $p['stock'] === 0): ?>
                <span class="stock stock-out">Out of stock</span>
            <?php elseif ((int) $p['stock'] <= 5): ?>
                <span class="stock stock-low">Only <?= (int) $p['stock'] ?> left</span>
            <?php endif; ?>
        </div>
        <form method="post" action="<?= url('/cart') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button class="btn btn-block" type="submit" <?= (int) $p['stock'] === 0 ? 'disabled' : '' ?>>Add to cart</button>
        </form>
    </div>
</article>
