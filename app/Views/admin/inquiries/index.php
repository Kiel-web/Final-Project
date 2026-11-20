<div class="page-head">
    <h1>Adoption inquiries</h1>
    <div class="chips">
        <a class="chip <?= $status === '' ? 'chip-active' : '' ?>" href="<?= url('/admin/inquiries') ?>">All</a>
        <?php foreach (['new', 'contacted', 'closed'] as $s): ?>
            <a class="chip <?= $status === $s ? 'chip-active' : '' ?>" href="<?= url('/admin/inquiries', ['status' => $s]) ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($inquiries)): ?>
    <p class="muted">No inquiries found.</p>
<?php endif; ?>

<?php foreach ($inquiries as $i): ?>
    <article class="card card-pad inquiry">
        <header>
            <strong><?= e($i['name']) ?></strong> about
            <a href="<?= url('/adopt/' . $i['pet_id']) ?>" target="_blank"><?= e($i['pet_name']) ?> (<?= e($i['breed_name']) ?>)</a>
            <span class="status-pill status-<?= e($i['status']) ?>"><?= e(ucfirst($i['status'])) ?></span>
        </header>
        <p class="muted small"><?= e($i['email']) ?><?= $i['phone'] ? ' · ' . e($i['phone']) : '' ?> · <?= e(date('M j, Y g:i A', strtotime($i['created_at']))) ?></p>
        <p><?= nl2br(e($i['message'])) ?></p>
        <form method="post" action="<?= url('/admin/inquiries/' . $i['id'] . '/status') ?>" class="inline-row">
            <?= csrf_field() ?><?= method_field('PUT') ?>
            <select name="status">
                <?php foreach (['new', 'contacted', 'closed'] as $s): ?>
                    <option value="<?= $s ?>" <?= $i['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-small" type="submit">Update</button>
        </form>
    </article>
<?php endforeach; ?>
