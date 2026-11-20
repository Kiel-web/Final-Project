<h1>My orders</h1>

<?php if (empty($orders)): ?>
    <div class="empty">
        <p>📦 You have not placed any orders yet.</p>
        <a class="btn" href="<?= url('/shop') ?>">Go shopping</a>
    </div>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><a href="<?= url('/orders/' . $o['id']) ?>">#<?= (int) $o['id'] ?></a></td>
                <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                <td><?= (int) $o['item_count'] ?></td>
                <td><?= money($o['total']) ?></td>
                <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
