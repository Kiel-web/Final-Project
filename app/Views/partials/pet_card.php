<article class="card pet-card pet-<?= e($pet['status']) ?>">
    <a href="<?= url('/adopt/' . $pet['id']) ?>" class="thumb-link">
        <?php if (!empty($pet['image_url'])): ?>
            <img class="pet-photo" src="<?= e($pet['image_url']) ?>" alt="<?= e($pet['name']) ?> the <?= e($pet['breed_name']) ?>" loading="lazy">
        <?php else: ?>
            <div class="pet-photo pet-photo-placeholder" data-photo-endpoint="<?= url('/api/pets/' . (int) $pet['id'] . '/photo') ?>" data-alt="<?= e($pet['name']) ?> the <?= e($pet['breed_name']) ?>"><span>🐱</span></div>
        <?php endif; ?>
        <span class="status-pill status-<?= e($pet['status']) ?>"><?= e(ucfirst($pet['status'])) ?></span>
    </a>
    <div class="card-body">
        <h3><a href="<?= url('/adopt/' . $pet['id']) ?>"><?= e($pet['name']) ?></a></h3>
        <p class="muted"><?= e($pet['breed_name']) ?> · <?= e(ucfirst($pet['gender'])) ?> · <?= e(format_age($pet['age_months'])) ?></p>
        <p class="muted small">Adoption fee: <strong><?= money($pet['adoption_fee']) ?></strong></p>
    </div>
</article>
