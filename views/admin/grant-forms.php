<?php
$preparedFields = array_map(static function (array $field): array {
    return [
        'section_key' => $field['section_key'],
        'section_title' => $field['section_title'],
        'field_key' => $field['field_key'],
        'label' => $field['label'],
        'field_type' => $field['field_type'],
        'help_text' => $field['help_text'],
        'placeholder' => $field['placeholder'],
        'options' => implode("\n", json_decode((string) ($field['options_json'] ?? ''), true) ?: []),
        'validation' => json_decode((string) ($field['validation_json'] ?? ''), true) ?: [],
        'is_required' => (int) $field['is_required'] === 1,
        'width' => $field['width'],
    ];
}, $fields);
?>
<header class="admin-page-header">
    <div><p class="text-sm text-muted">FIYFF Foundation</p><h1>Grant forms</h1></div>
    <a class="button button-primary" href="<?= e(url('/admin/grant-forms?new=1')) ?>">Create grant form</a>
</header>

<div class="admin-split">
    <section class="space-y-3">
        <?php foreach ($forms as $item): ?>
            <a class="admin-list-card <?= $editing && (int) $editing['id'] === (int) $item['id'] ? 'is-active' : '' ?>" href="<?= e(url('/admin/grant-forms?edit=' . $item['id'])) ?>">
                <div class="min-w-0 flex-1"><strong class="block truncate text-wine"><?= e($item['title']) ?></strong><span class="mt-1 block text-xs text-muted"><?= e($item['event_title'] ?: 'No linked event') ?> · <?= (int) $item['application_count'] ?> applications</span></div>
                <span class="status status-<?= e($item['status']) ?>"><?= e($item['status']) ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (!$forms): ?><div class="empty-state">No managed grant forms yet.</div><?php endif; ?>
    </section>

    <section class="admin-card">
        <?php if ($editing || isset($_GET['new'])): ?>
            <div class="admin-card-header"><div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= $editing ? 'Edit application experience' : 'New grant form' ?></p><h2><?= e($editing['title'] ?? 'Create grant form') ?></h2></div><?php if ($editing && $editing['status'] === 'published'): ?><a href="<?= e(url('/grants/' . $editing['slug'] . '/apply')) ?>" target="_blank" rel="noopener">Open public form ↗</a><?php endif; ?></div>
            <form method="post" action="<?= e(url('/admin/grant-forms')) ?>" class="mt-6 space-y-7" data-unsaved-form data-grant-builder>
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                <input type="hidden" name="fields_json" value="" data-grant-fields-json>
                <script type="application/json" data-grant-fields-data><?= json_encode($preparedFields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

                <fieldset class="admin-fieldset"><legend>Form details</legend>
                    <div class="form-grid">
                        <div class="form-field md:col-span-2"><label>Title <span>*</span></label><input class="form-control" name="title" value="<?= e($editing['title'] ?? '') ?>" required></div>
                        <div class="form-field"><label>URL slug</label><input class="form-control" name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="Created from title"></div>
                        <div class="form-field"><label>Linked grant event <span>*</span></label><select class="form-control" name="event_id" required><option value="">Choose event</option><?php foreach ($events as $event): ?><option value="<?= (int) $event['id'] ?>" <?= (int) ($editing['event_id'] ?? 0) === (int) $event['id'] ? 'selected' : '' ?>><?= e($event['title']) ?></option><?php endforeach; ?></select><p class="field-help">Create an event with type “Grant Program” first if it is not listed.</p></div>
                        <div class="form-field md:col-span-2"><label>Introduction <span>*</span></label><textarea class="form-control min-h-32" name="intro" required><?= e($editing['intro'] ?? '') ?></textarea></div>
                        <div class="form-field md:col-span-2"><label>Eligibility notice</label><textarea class="form-control min-h-24" name="eligibility_notice"><?= e($editing['eligibility_notice'] ?? '') ?></textarea></div>
                        <div class="form-field md:col-span-2"><label>Success message</label><textarea class="form-control min-h-24" name="success_message"><?= e($editing['success_message'] ?? '') ?></textarea></div>
                    </div>
                </fieldset>

                <fieldset class="admin-fieldset"><legend>Availability and email</legend>
                    <div class="form-grid">
                        <div class="form-field"><label>Opens at</label><input class="form-control" type="datetime-local" name="opens_at" value="<?= !empty($editing['opens_at']) ? e(date('Y-m-d\TH:i', strtotime($editing['opens_at']))) : '' ?>"></div>
                        <div class="form-field"><label>Closes at</label><input class="form-control" type="datetime-local" name="closes_at" value="<?= !empty($editing['closes_at']) ? e(date('Y-m-d\TH:i', strtotime($editing['closes_at']))) : '' ?>"></div>
                        <div class="form-field"><label>Status</label><select class="form-control" name="status"><?php foreach (['draft','published','closed'] as $status): ?><option value="<?= e($status) ?>" <?= ($editing['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></div>
                        <div class="form-field"><label>New-application notification email</label><input class="form-control" type="email" name="notification_email" value="<?= e($editing['notification_email'] ?? '') ?>" placeholder="<?= e(setting('smtp_admin_email')) ?>"><p class="field-help">Falls back to the SMTP admin email.</p></div>
                        <label class="consent-row md:col-span-2"><input type="checkbox" name="allow_save_progress" value="1" <?= !isset($editing['allow_save_progress']) || (int) $editing['allow_save_progress'] === 1 ? 'checked' : '' ?>><span>Allow applicants to save typed answers on their current device.</span></label>
                    </div>
                </fieldset>

                <fieldset class="admin-fieldset"><legend>Application fields</legend>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><p class="field-help max-w-xl">Group fields with the same section title. Field keys are permanent identifiers such as <strong>first_name</strong> or <strong>medical_history</strong>.</p><button class="button button-secondary shrink-0" type="button" data-grant-add-field>Add field</button></div>
                    <div class="mt-5 space-y-4" data-grant-field-list></div>
                </fieldset>

                <div class="admin-savebar">
                    <button class="button button-primary" type="submit">Save grant form</button>
                    <?php if ($editing): ?><span class="text-xs text-muted">Public URL: /grants/<?= e($editing['slug']) ?>/apply</span><?php endif; ?>
                </div>
            </form>
            <?php if ($editing): ?><form method="post" action="<?= e(url('/admin/grant-forms')) ?>" class="mt-6 border-t border-line pt-6"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><input type="hidden" name="action" value="delete"><button class="text-sm font-bold text-red-700" data-confirm="Delete this form? This is available only before applications exist.">Delete form</button></form><?php endif; ?>
        <?php else: ?>
            <div class="empty-state">Select a form to edit it, or create a new grant form.</div>
        <?php endif; ?>
    </section>
</div>
