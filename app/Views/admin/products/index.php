<div class="page-head">
    <h1>Products</h1>
    <a class="btn" href="<?= url('/admin/products/create') ?>">+ Add product</a>
</div>

<table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category_icon']) ?> <?= e($p['category_name']) ?></td>
            <td><?= money($p['price']) ?></td>
            <td><span class="stock <?= (int) $p['stock'] === 0 ? 'stock-out' : ((int) $p['stock'] <= 5 ? 'stock-low' : '') ?>"><?= (int) $p['stock'] ?></span></td>
            <td><?= $p['is_featured'] ? '⭐' : '' ?></td>
            <td class="actions">
                <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>">Edit</a>
                <form method="post" action="<?= url('/admin/products/' . $p['id']) ?>" data-confirm="Delete &quot;<?= e($p['name']) ?>&quot;?">
                    <?= csrf_field() ?><?= method_field('DELETE') ?>
                    <button class="link-button danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
