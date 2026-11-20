<?php
$editing = $pet !== null;
$action  = $editing ? url('/admin/pets/' . $pet['id']) : url('/admin/pets');
$val = function (string $field, $default = '') use ($pet) {
    return old($field, $pet[$field] ?? $default);
};
?>
<div class="page-head">
    <h1><?= $editing ? 'Edit ' . e($pet['name']) : 'Add a cat for adoption' ?></h1>
    <a href="<?= url('/admin/pets') ?>">← Back to list</a>
</div>

<form method="post" action="<?= $action ?>" class="form card card-pad narrow">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif; ?>

    <div class="row-2">
        <label>Name
            <input type="text" name="name" value="<?= e($val('name')) ?>" required>
            <?= field_error('name') ?>
        </label>
        <label>Breed <small class="muted">(list loaded from the Cat API)</small>
            <select name="breed_id" required>
                <option value="">Choose…</option>
                <?php foreach ($breeds as $id => $name): ?>
                    <option value="<?= e($id) ?>" <?= (string) $val('breed_id') === (string) $id ? 'selected' : '' ?>><?= e($name) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error('breed_id') ?>
        </label>
    </div>

    <div class="row-3">
        <label>Age (months)
            <input type="number" name="age_months" min="0" max="300" value="<?= e($val('age_months', 12)) ?>" required>
            <?= field_error('age_months') ?>
        </label>
        <label>Gender
            <select name="gender">
                <option value="female" <?= $val('gender', 'female') === 'female' ? 'selected' : '' ?>>Female</option>
                <option value="male"   <?= $val('gender') === 'male' ? 'selected' : '' ?>>Male</option>
            </select>
            <?= field_error('gender') ?>
        </label>
        <label>Color
            <input type="text" name="color" value="<?= e($val('color')) ?>" required>
            <?= field_error('color') ?>
        </label>
    </div>

    <label>Description
        <textarea name="description" rows="4" required><?= e($val('description')) ?></textarea>
        <?= field_error('description') ?>
    </label>

    <div class="row-2">
        <label>Adoption fee
            <input type="number" name="adoption_fee" step="0.01" min="0" value="<?= e($val('adoption_fee', 0)) ?>" required>
            <?= field_error('adoption_fee') ?>
        </label>
        <label>Status
            <select name="status">
                <?php foreach (['available', 'pending', 'adopted'] as $s): ?>
                    <option value="<?= $s ?>" <?= $val('status', 'available') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error('status') ?>
        </label>
    </div>

    <label>Photo URL (optional)
        <input type="text" name="image_url" value="<?= e($val('image_url')) ?>" placeholder="Leave empty - a photo of this breed is fetched from the Cat API automatically">
        <?= field_error('image_url') ?>
    </label>

    <button class="btn btn-large" type="submit"><?= $editing ? 'Save changes' : 'Add cat' ?></button>
</form>
