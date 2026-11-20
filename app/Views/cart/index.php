<h1>Your cart</h1>

<?php if (empty($items)): ?>
    <div class="empty">
        <p>🛒 Your cart is empty.</p>
        <a class="btn" href="<?= url('/shop') ?>">Start shopping</a>
    </div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): $p = $item['product']; ?>
            <tr>
                <td><a href="<?= url('/shop/' . $p['id']) ?>"><?= e($p['category_icon']) ?> <?= e($p['name']) ?></a></td>
                <td><?= money($p['price']) ?></td>
                <td>
                    <form method="post" action="<?= url('/cart/' . $p['id']) ?>" class="inline-row">
                        <?= csrf_field() ?><?= method_field('PUT') ?>
                        <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="<?= (int) $p['stock'] ?>" class="qty">
                        <button class="btn btn-small btn-outline" type="submit">Update</button>
                    </form>
                </td>
                <td><strong><?= money($item['subtotal']) ?></strong></td>
                <td>
                    <form method="post" action="<?= url('/cart/' . $p['id']) ?>">
                        <?= csrf_field() ?><?= method_field('DELETE') ?>
                        <button class="link-button danger" type="submit">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="right">Total</td><td colspan="2"><strong class="price-big"><?= money($total) ?></strong></td></tr>
        </tfoot>
    </table>

    <p class="right">
        <a class="btn btn-outline" href="<?= url('/shop') ?>">Continue shopping</a>
        <a class="btn btn-large" href="<?= url('/checkout') ?>">Checkout →</a>
    </p>
<?php endif; ?>
