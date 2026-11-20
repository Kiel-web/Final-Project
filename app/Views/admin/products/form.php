<?php
$editing = $product !== null;
$action  = $editing ? url('/admin/products/' . $product['id']) : url('/admin/products');
$val = function (string $field, $default = '') use ($product) {
    return old($field, $product[$field] ?? $default);
};
?>
<div class="page-head">
    <h1><?= $editing ? 'Edit product' : 'Add product' ?></h1>
    <a href="<?= url('/admin/products') ?>">← Back to products</a>
</div>

<form method="post" action="<?= $action ?>" class="form card card-pad narrow">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>

    <label>Name
        <input type="text" name="name" value="<?= e($val('name')) ?>" required>
        <?= field_error('name') ?>
    </label>

    <label>Category
        <select name="category_id" required>
            <option value="">Choose…</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) $val('category_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['icon']) ?> <?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error('category_id') ?>
    </label>

    <label>Description
        <textarea name="description" rows="4" required><?= e($val('description')) ?></textarea>
        <?= field_error('description') ?>
    </label>

    <div class="row-2">
        <label>Price
            <input type="number" name="price" step="0.01" min="0" value="<?= e($val('price')) ?>" required>
            <?= field_error('price') ?>
        </label>
        <label>Stock
            <input type="number" name="stock" min="0" value="<?= e($val('stock', 0)) ?>" required>
            <?= field_error('stock') ?>
        </label>
    </div>

    <label>Image URL (optional)
        <input type="text" name="image_url" value="<?= e($val('image_url')) ?>" placeholder="https://…  (leave empty to show the category icon)">
        <?= field_error('image_url') ?>
    </label>

    <label class="checkbox">
        <input type="checkbox" name="is_featured" value="1" <?= $val('is_featured', 0) ? 'checked' : '' ?>> Show on the home page (featured)
    </label>

    <button class="btn btn-large" type="submit"><?= $editing ? 'Save changes' : 'Add product' ?></button>
</form>
