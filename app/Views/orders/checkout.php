<h1>Checkout</h1>

<div class="two-col">
    <form method="post" action="<?= url('/checkout') ?>" class="form card card-pad">
        <?= csrf_field() ?>
        <h2>Delivery details</h2>

        <label>Full name
            <input type="text" name="shipping_name" value="<?= e(old('shipping_name', $user['name'])) ?>" required>
            <?= field_error('shipping_name') ?>
        </label>
        <label>Address
            <input type="text" name="shipping_address" value="<?= e(old('shipping_address')) ?>" placeholder="Street, city" required>
            <?= field_error('shipping_address') ?>
        </label>
        <label>Phone
            <input type="text" name="phone" value="<?= e(old('phone')) ?>" required>
            <?= field_error('phone') ?>
        </label>
        <label>Payment method
            <select name="payment_method">
                <option value="cod"    <?= old('payment_method', 'cod') === 'cod' ? 'selected' : '' ?>>Cash on delivery</option>
                <option value="pickup" <?= old('payment_method') === 'pickup' ? 'selected' : '' ?>>Pay at store pickup</option>
            </select>
            <?= field_error('payment_method') ?>
        </label>

        <button class="btn btn-large btn-block" type="submit">Place order</button>
    </form>

    <aside class="card card-pad">
        <h2>Order summary</h2>
        <ul class="summary">
            <?php foreach ($items as $item): ?>
                <li><span><?= e($item['product']['name']) ?> × <?= (int) $item['quantity'] ?></span><strong><?= money($item['subtotal']) ?></strong></li>
            <?php endforeach; ?>
        </ul>
        <p class="summary-total"><span>Total</span> <strong><?= money($total) ?></strong></p>
    </aside>
</div>
