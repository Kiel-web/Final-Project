<p class="breadcrumb"><a href="<?= url('/adopt') ?>">Adopt</a> › <?= e($pet['name']) ?></p>

<section class="detail">
    <div class="detail-media">
        <?php if (!empty($pet['image_url'])): ?>
            <img class="pet-photo pet-photo-big" src="<?= e($pet['image_url']) ?>" alt="<?= e($pet['name']) ?> the <?= e($pet['breed_name']) ?>">
        <?php else: ?>
            <div class="pet-photo pet-photo-big pet-photo-placeholder"><span>🐱</span></div>
        <?php endif; ?>
        <p class="muted small">Photo: TheCatAPI (a typical <?= e($pet['breed_name']) ?>)</p>
    </div>

    <div class="detail-info">
        <span class="status-pill status-<?= e($pet['status']) ?>"><?= e(ucfirst($pet['status'])) ?></span>
        <h1><?= e($pet['name']) ?></h1>
        <p class="muted"><?= e($pet['breed_name']) ?> · <?= e(ucfirst($pet['gender'])) ?> · <?= e(format_age($pet['age_months'])) ?> · <?= e($pet['color']) ?></p>
        <p><?= nl2br(e($pet['description'])) ?></p>
        <p>Adoption fee: <strong class="price"><?= money($pet['adoption_fee']) ?></strong></p>
    </div>
</section>

<section class="two-col">
    <div class="card card-pad">
        <h2>About the <?= e($pet['breed_name']) ?> breed</h2>

        <?php if ($breed): ?>
            <?php if (!empty($breed['description'])): ?><p><?= e($breed['description']) ?></p><?php endif; ?>
            <dl class="facts">
                <?php if (!empty($breed['origin'])): ?><dt>Origin</dt><dd><?= e($breed['origin']) ?></dd><?php endif; ?>
                <?php if (!empty($breed['temperament'])): ?><dt>Temperament</dt><dd><?= e($breed['temperament']) ?></dd><?php endif; ?>
                <?php if (!empty($breed['life_span'])): ?><dt>Life span</dt><dd><?= e($breed['life_span']) ?> years</dd><?php endif; ?>
                <?php if (!empty($breed['weight']['metric'])): ?><dt>Weight</dt><dd><?= e($breed['weight']['metric']) ?> kg</dd><?php endif; ?>
                <?php foreach (['affection_level' => 'Affection', 'energy_level' => 'Energy', 'intelligence' => 'Intelligence', 'child_friendly' => 'Child friendly', 'dog_friendly' => 'Dog friendly'] as $key => $label): ?>
                    <?php if (isset($breed[$key])): ?><dt><?= e($label) ?></dt><dd><?= rating_dots($breed[$key]) ?></dd><?php endif; ?>
                <?php endforeach; ?>
            </dl>
        <?php else: ?>
            <p class="muted">Breed information is not available right now<?= $apiProblem ? ' (' . e($apiProblem) . ')' : '' ?>. Please check back later.</p>
        <?php endif; ?>
    </div>

    <div class="card card-pad" id="inquiry">
        <h2>Interested in <?= e($pet['name']) ?>?</h2>

        <?php if ($pet['status'] !== 'available'): ?>
            <p class="muted"><?= e($pet['name']) ?> is <strong><?= e($pet['status']) ?></strong> and cannot receive new inquiries.
                <a href="<?= url('/adopt', ['status' => 'available']) ?>">See available cats</a>.</p>
        <?php else: ?>
            <form method="post" action="<?= url('/adopt/' . $pet['id'] . '/inquire') ?>" class="form">
                <?= csrf_field() ?>
                <label>Your name
                    <input type="text" name="name" value="<?= e(old('name', $user['name'] ?? '')) ?>" required>
                    <?= field_error('name') ?>
                </label>
                <label>Email
                    <input type="email" name="email" value="<?= e(old('email', $user['email'] ?? '')) ?>" required>
                    <?= field_error('email') ?>
                </label>
                <label>Phone (optional)
                    <input type="text" name="phone" value="<?= e(old('phone')) ?>">
                    <?= field_error('phone') ?>
                </label>
                <label>Message
                    <textarea name="message" rows="4" placeholder="Tell us about your home and why you would like to adopt <?= e($pet['name']) ?>…" required><?= e(old('message')) ?></textarea>
                    <?= field_error('message') ?>
                </label>
                <button class="btn btn-large btn-block" type="submit">Send inquiry</button>
            </form>
        <?php endif; ?>
    </div>
</section>
