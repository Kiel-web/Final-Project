<h1>Adopt a cat</h1>
<p class="muted">Every cat below is looking for a loving home. Click a cat to read its story and send an adoption inquiry.</p>

<form class="filter-bar" method="get" action="<?= url('/adopt') ?>">
    <input type="search" name="q" value="<?= e($term) ?>" placeholder="Search by name, breed or color…">
    <select name="gender">
        <option value="">Any gender</option>
        <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
        <option value="male"   <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
    </select>
    <select name="status">
        <option value="">Any status</option>
        <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
        <option value="pending"   <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="adopted"   <?= $status === 'adopted' ? 'selected' : '' ?>>Adopted</option>
    </select>
    <button class="btn" type="submit">Filter</button>
    <?php if ($term !== '' || $gender !== '' || $status !== ''): ?><a href="<?= url('/adopt') ?>" class="muted">Clear</a><?php endif; ?>
</form>

<?php if (empty($pets)): ?>
    <div class="empty"><p>😿 No cats match your filters.</p><a class="btn" href="<?= url('/adopt') ?>">Show all cats</a></div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($pets as $pet): ?>
            <?= partial('partials/pet_card', ['pet' => $pet]) ?>
        <?php endforeach; ?>
    </div>
    <?= partial('partials/pagination', ['paging' => $paging, 'path' => '/adopt', 'query' => ['q' => $term, 'gender' => $gender, 'status' => $status]]) ?>
<?php endif; ?>
