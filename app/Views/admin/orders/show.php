<div class="page-head">
    <h1>Order #<?= (int) $order['id'] ?> <span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></h1>
    <a href="<?= url('/admin/orders') ?>">← All orders</a>
</div>

<div class="two-col">
    <table class="table">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td><?= e($i['product_name']) ?></td>
                <td><?= money($i['unit_price']) ?></td>
                <td><?= (int) $i['quantity'] ?></td>
                <td><?= money($i['unit_price'] * $i['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="3" class="right">Total</td><td><strong><?= money($order['total']) ?></strong></td></tr></tfoot>
    </table>

    <aside>
        <div class="card card-pad">
            <h3>Customer</h3>
            <p><?= e($order['customer_name']) ?><br><small class="muted"><?= e($order['customer_email']) ?></small></p>
            <h3>Delivery</h3>
            <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['phone']) ?></p>
            <p class="muted small">Payment: <?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Store pickup' ?><br>
            Placed: <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
        </div>

        <div class="card card-pad">
            <h3>Update status</h3>
            <form method="post" action="<?= url('/admin/orders/' . $order['id'] . '/status') ?>" class="form">
                <?= csrf_field() ?><?= method_field('PUT') ?>
                <select name="status">
                    <?php foreach (\App\Models\Order::STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn" type="submit">Save status</button>
                <p class="muted small">Cancelling an order puts the products back in stock.</p>
            </form>
        </div>
    </aside>
</div>
