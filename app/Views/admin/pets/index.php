<div class="page-head">
    <h1>Adoption listings</h1>
    <a class="btn" href="<?= url('/admin/pets/create') ?>">+ Add cat</a>
</div>

<table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Breed</th><th>Age</th><th>Gender</th><th>Fee</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pets as $p): ?>
        <tr>
            <td><?= (int) $p['id'] ?></td>
            <td><a href="<?= url('/adopt/' . $p['id']) ?>" target="_blank"><?= e($p['name']) ?></a></td>
            <td><?= e($p['breed_name']) ?></td>
            <td><?= e(format_age($p['age_months'])) ?></td>
            <td><?= e(ucfirst($p['gender'])) ?></td>
            <td><?= money($p['adoption_fee']) ?></td>
            <td><span class="status-pill status-<?= e($p['status']) ?>"><?= e(ucfirst($p['status'])) ?></span></td>
            <td class="actions">
                <a href="<?= url('/admin/pets/' . $p['id'] . '/edit') ?>">Edit</a>
                <form method="post" action="<?= url('/admin/pets/' . $p['id']) ?>" data-confirm="Remove <?= e($p['name']) ?> from the adoption list?">
                    <?= csrf_field() ?><?= method_field('DELETE') ?>
                    <button class="link-button danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
