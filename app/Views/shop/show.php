<p class="breadcrumb"><a href="<?= url('/shop') ?>">Shop</a> › <a href="<?= url('/shop', ['category' => $product['category_id']]) ?>"><?= e($product['category_name']) ?></a> › <?= e($product['name']) ?></p>

<section class="detail">
    <div class="detail-media">
        <?php if (!empty($product['image_url'])): ?>
            <img class="thumb-img" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
            <div class="thumb thumb-<?= (int) $product['category_id'] % 6 ?> thumb-big"><span><?= e($product['category_icon']) ?></span></div>
        <?php endif; ?>
    </div>

    <div class="detail-info">
        <span class="tag"><?= e($product['category_name']) ?></span>
        <h1><?= e($product['name']) ?></h1>
        <p class="price price-big"><?= money($product['price']) ?></p>
        <p><?= nl2br(e($product['description'])) ?></p>

        <?php if ((int) $product['stock'] === 0): ?>
            <p><span class="stock stock-out">Out of stock</span></p>
        <?php else: ?>
            <p class="muted"><?= (int) $product['stock'] ?> in stock</p>
            <form method="post" action="<?= url('/cart') ?>" class="inline-row">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <label>Quantity
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int) $product['stock'] ?>" class="qty">
                </label>
                <button class="btn btn-large" type="submit">Add to cart</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($related)): ?>
<section>
    <h2>You may also like</h2>
    <div class="grid">
        <?php foreach ($related as $p): ?>
            <?= partial('partials/product_card', ['p' => $p]) ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
