<?php
$testimonial = $editing ?: [
    'id' => '',
    'client_name' => '',
    'submitter_email' => '',
    'photo_path' => '',
    'photo_alt' => '',
    'quote' => '',
    'source' => 'admin',
    'approval_status' => 'approved',
    'sort_order' => 0,
    'is_visible' => 1,
    'created_at' => '',
];
$submissionsEnabled = (bool) ($testimonialSettings['enabled'] ?? true);
$autoApprove = (bool) ($testimonialSettings['auto_approve'] ?? false);
?>

<header class="admin-page-header">
    <div>
        <p class="text-sm text-muted">Community stories and moderation</p>
        <h1>Testimonials</h1>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="button button-secondary" href="<?= e(url('/testimonials')) ?>" target="_blank" rel="noopener">View public page</a>
        <a class="button button-primary" href="<?= e(url('/admin/testimonials?new=1')) ?>">+ Add testimonial</a>
    </div>
</header>

<section class="admin-card mb-6">
    <div class="admin-card-header">
        <div>
            <h2>Public submissions</h2>
            <p class="mt-1 text-sm text-muted">Choose whether visitors can submit stories and whether new stories require review.</p>
        </div>
    </div>
    <form method="post" action="<?= e(url('/admin/testimonials')) ?>" class="mt-5 space-y-4">
        <?= csrf_field() ?>
        <label class="consent-row">
            <input type="checkbox" name="testimonial_submissions_enabled" value="1" <?= $submissionsEnabled ? 'checked' : '' ?>>
            <span><strong>Accept public testimonial submissions</strong><small class="block text-muted">When disabled, the public form is hidden and submissions are rejected.</small></span>
        </label>
        <label class="consent-row">
            <input type="checkbox" name="testimonial_auto_approve" value="1" <?= $autoApprove ? 'checked' : '' ?>>
            <span><strong>Automatically approve public submissions</strong><small class="block text-muted">When off, new submissions stay pending and remain hidden until an admin approves them.</small></span>
        </label>
        <button class="button button-secondary" name="action" value="save_settings">Save moderation settings</button>
    </form>
</section>

<div class="admin-split">
    <section class="admin-card">
        <?php if (!$testimonials): ?>
            <p class="text-sm text-muted">No testimonials yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($testimonials as $item): ?>
                    <?php
                    $status = $item['approval_status'] ?? 'approved';
                    $statusClass = $status === 'approved' ? 'status-published' : ($status === 'rejected' ? 'status-draft' : 'status-pending');
                    ?>
                    <a class="admin-list-card <?= (int) ($testimonial['id'] ?: 0) === (int) $item['id'] ? 'is-active' : '' ?>" href="<?= e(url('/admin/testimonials?edit=' . $item['id'])) ?>">
                        <?php if ($item['photo_path']): ?>
                            <img src="<?= e(media_url($item['photo_path'])) ?>" alt="" class="size-14 rounded-full object-cover">
                        <?php else: ?>
                            <span class="grid size-14 place-items-center rounded-full bg-blush font-bold text-wine"><?= e(mb_substr($item['client_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-wine"><?= e($item['client_name']) ?></p>
                            <p class="mt-1 truncate text-xs text-muted"><?= e($item['quote']) ?></p>
                            <p class="mt-1 text-[11px] uppercase tracking-[.12em] text-muted"><?= ($item['source'] ?? 'admin') === 'public' ? 'Public submission' : 'Admin post' ?></p>
                        </div>
                        <span class="status <?= e($statusClass) ?>"><?= e(ucfirst($status)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2><?= $testimonial['id'] ? 'Review testimonial' : 'Add testimonial' ?></h2>
                <?php if (($testimonial['source'] ?? 'admin') === 'public' && $testimonial['created_at']): ?>
                    <p class="mt-1 text-xs text-muted">Submitted <?= e(date('j M Y, g:i a', strtotime($testimonial['created_at']))) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/testimonials')) ?>" class="mt-6 space-y-6" data-unsaved-form>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($testimonial['id']) ?>">

            <?php if (!empty($testimonial['submitter_email'])): ?>
                <div class="rounded-2xl border border-border bg-cream/40 p-4 text-sm">
                    <p class="font-semibold text-wine">Private submitter contact</p>
                    <a class="mt-1 inline-block text-rose underline" href="mailto:<?= e($testimonial['submitter_email']) ?>"><?= e($testimonial['submitter_email']) ?></a>
                </div>
            <?php endif; ?>

            <?php if ($testimonial['photo_path']): ?>
                <img src="<?= e(media_url($testimonial['photo_path'])) ?>" alt="" class="size-28 rounded-2xl object-cover">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-field md:col-span-2">
                    <label>Client name <span>*</span></label>
                    <input class="form-control" name="client_name" maxlength="190" value="<?= e($testimonial['client_name']) ?>" required>
                </div>
                <div class="form-field">
                    <label>Upload photo</label>
                    <input class="file-control" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                </div>
                <div class="form-field">
                    <label>Or photo path / URL</label>
                    <input class="form-control" type="text" name="photo_url" value="<?= e($testimonial['photo_path']) ?>" placeholder="/uploads/… or https://…">
                </div>
                <div class="form-field md:col-span-2">
                    <label>Photo alt text</label>
                    <input class="form-control" name="photo_alt" value="<?= e($testimonial['photo_alt']) ?>">
                    <p class="field-help">Required only when a photo is present.</p>
                </div>
                <?php if ($testimonial['photo_path']): ?>
                    <label class="consent-row md:col-span-2"><input type="checkbox" name="remove_photo" value="1"><span>Remove this testimonial photo</span></label>
                <?php endif; ?>
                <div class="form-field md:col-span-2">
                    <label>Quote <span>*</span></label>
                    <textarea class="form-control min-h-40" name="quote" maxlength="1000" required><?= e($testimonial['quote']) ?></textarea>
                </div>
                <div class="form-field">
                    <label>Approval status</label>
                    <select class="form-control" name="approval_status">
                        <?php foreach (['pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($testimonial['approval_status'] ?? 'approved') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Display order</label>
                    <input class="form-control" type="number" name="sort_order" value="<?= e($testimonial['sort_order']) ?>">
                </div>
                <label class="consent-row md:col-span-2">
                    <input type="checkbox" name="is_visible" value="1" <?= ($testimonial['is_visible'] || ($testimonial['approval_status'] ?? '') === 'pending') ? 'checked' : '' ?>>
                    <span>Show on the website once approved</span>
                </label>
            </div>

            <div class="admin-savebar">
                <button class="button button-primary" name="action" value="save">Save testimonial</button>
                <?php if ($testimonial['id']): ?>
                    <button class="button button-danger ml-auto" name="action" value="delete" data-confirm="Delete this testimonial?">Delete</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</div>
