<?php
if ($paging['pages'] <= 1) {
    return;
}
$query = array_filter($query ?? [], function ($v) {
    return $v !== '' && $v !== null;
});
?>
<nav class="pagination" aria-label="Pages">
    <?php if ($paging['page'] > 1): ?>
        <a href="<?= url($path, array_merge($query, ['page' => $paging['page'] - 1])) ?>">← Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $paging['pages']; $i++): ?>
        <a class="<?= $i === $paging['page'] ? 'current' : '' ?>" href="<?= url($path, array_merge($query, ['page' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($paging['page'] < $paging['pages']): ?>
        <a href="<?= url($path, array_merge($query, ['page' => $paging['page'] + 1])) ?>">Next →</a>
    <?php endif; ?>
</nav>
