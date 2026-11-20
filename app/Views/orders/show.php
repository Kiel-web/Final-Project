<p class="breadcrumb"><a href="<?= url('/orders') ?>">My orders</a> › #<?= (int) $order['id'] ?></p>
<h1>Order #<?= (int) $order['id'] ?> <span class="status-pill status-<?= e($order['status']) ?>"><?= e(ucfirst($order['status'])) ?></span></h1>

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

    <aside class="card card-pad">
        <h3>Delivery</h3>
        <p><?= e($order['shipping_name']) ?><br><?= e($order['shipping_address']) ?><br><?= e($order['phone']) ?></p>
        <p class="muted small">Payment: <?= $order['payment_method'] === 'cod' ? 'Cash on delivery' : 'Pay at store pickup' ?><br>
        Placed: <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
    </aside>
</div>
