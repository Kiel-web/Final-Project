<div class="page-head">
    <h1>Orders</h1>
    <form method="get" action="<?= url('/admin/orders') ?>" class="inline-row">
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (\App\Models\Order::STATUSES as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-small" type="submit">Filter</button></noscript>
    </form>
</div>

<?php if (empty($orders)): ?>
    <p class="muted">No orders found.</p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><a href="<?= url('/admin/orders/' . $o['id']) ?>">#<?= (int) $o['id'] ?></a></td>
            <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
            <td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['customer_email']) ?></small></td>
            <td><?= money($o['total']) ?></td>
            <td><?= $o['payment_method'] === 'cod' ? 'Cash on delivery' : 'Store pickup' ?></td>
            <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
