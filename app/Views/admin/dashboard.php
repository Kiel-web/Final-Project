<h1>Dashboard</h1>

<div class="stats">
    <div class="stat"><span class="stat-num"><?= money($revenue) ?></span><span>Revenue (not cancelled)</span></div>
    <div class="stat"><span class="stat-num"><?= array_sum($orderCounts) ?></span><span>Orders · <?= (int) $orderCounts['pending'] ?> pending</span></div>
    <div class="stat"><span class="stat-num"><?= (int) $productCount ?></span><span>Products</span></div>
    <div class="stat"><span class="stat-num"><?= (int) $petCounts['available'] ?></span><span>Cats available · <?= (int) $petCounts['pending'] ?> pending · <?= (int) $petCounts['adopted'] ?> adopted</span></div>
    <div class="stat"><span class="stat-num"><?= (int) $newInquiries ?></span><span><a href="<?= url('/admin/inquiries', ['status' => 'new']) ?>">New adoption inquiries</a></span></div>
</div>

<div class="two-col">
    <section>
        <h2>Recent orders</h2>
        <?php if (empty($recentOrders)): ?>
            <p class="muted">No orders yet.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><a href="<?= url('/admin/orders/' . $o['id']) ?>">#<?= (int) $o['id'] ?></a></td>
                        <td><?= e($o['customer_name']) ?></td>
                        <td><?= money($o['total']) ?></td>
                        <td><span class="status-pill status-<?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Low stock</h2>
        <?php if (empty($lowStock)): ?>
            <p class="muted">All products are well stocked.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                <tbody>
                <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>"><?= e($p['name']) ?></a></td>
                        <td><span class="stock <?= (int) $p['stock'] === 0 ? 'stock-out' : 'stock-low' ?>"><?= (int) $p['stock'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
