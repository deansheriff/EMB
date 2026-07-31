<header class="admin-page-header">
    <div><p class="text-sm text-muted">FIYFF Foundation</p><h1>Grant applications</h1></div>
    <div class="flex flex-wrap gap-3"><a class="button button-secondary" href="<?= e(url('/admin/grant-forms')) ?>">Manage forms</a><button class="button button-secondary" type="button" data-export-table="#grant-table">Export CSV</button></div>
</header>

<section class="admin-metric-grid mb-7">
    <?php foreach (['submitted','in_review','shortlisted','declined','awarded'] as $status): ?>
        <div class="metric-card"><span class="text-sm capitalize text-muted"><?= e(str_replace('_', ' ', $status)) ?></span><strong class="mt-4 block font-display text-3xl text-wine"><?= (int) ($counts[$status] ?? 0) ?></strong></div>
    <?php endforeach; ?>
</section>

<div class="admin-inbox">
    <section class="admin-card p-0">
        <div class="admin-table-wrap"><table id="grant-table" class="admin-table"><thead><tr><th>Applicant</th><th>Grant</th><th>Submitted</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($applications as $item): ?><tr class="<?= $selected && (int) $selected['id'] === (int) $item['id'] ? 'bg-blush/40' : '' ?>">
                <td><a class="font-semibold text-wine hover:underline" href="<?= e(url('/admin/grant-applications?view=' . $item['id'])) ?>"><?= e($item['full_name']) ?></a><span class="block text-xs text-muted"><?= e($item['applicant_code']) ?> · <?= e($item['location']) ?></span></td>
                <td><?= e($item['event_title']) ?></td>
                <td><?= e(format_date($item['created_at'])) ?></td>
                <td><span class="status status-<?= e($item['status']) ?>"><?= e(str_replace('_', ' ', $item['status'])) ?></span></td>
            </tr><?php endforeach; ?>
        </tbody></table></div>
        <?php if (!$applications): ?><div class="empty-state m-5">No grant applications yet.</div><?php endif; ?>
    </section>

    <section class="admin-card">
        <?php if ($selected):
            $answers = json_decode((string) $selected['answers_json'], true) ?: [];
            $snapshot = json_decode((string) ($selected['form_snapshot_json'] ?? ''), true) ?: [];
            if (!$snapshot) {
                $snapshot = [
                    ['key' => 'journey_summary', 'label' => 'Journey summary', 'section_title' => 'Your journey', 'type' => 'textarea'],
                    ['key' => 'support_need', 'label' => 'How support would help', 'section_title' => 'Your journey', 'type' => 'textarea'],
                    ['key' => 'clinic_status', 'label' => 'Clinic status', 'section_title' => 'Your journey', 'type' => 'select'],
                ];
            }
            $answerSections = [];
            foreach ($snapshot as $field) {
                if (($field['type'] ?? '') === 'file') {
                    continue;
                }
                $sectionTitle = (string) ($field['section_title'] ?? 'Application');
                $answerSections[$sectionTitle][] = $field;
            }
            $documentLabels = [];
            foreach ($snapshot as $field) {
                $documentLabels[$field['key'] ?? ''] = $field['label'] ?? 'Document';
            }
        ?>
            <div class="admin-card-header"><div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= e($selected['applicant_code']) ?></p><h2 class="mt-1"><?= e($selected['full_name']) ?></h2><p class="mt-1 text-xs text-muted"><?= e($selected['event_title']) ?></p></div><span class="status status-<?= e($selected['status']) ?>"><?= e(str_replace('_', ' ', $selected['status'])) ?></span></div>
            <div class="mt-6 rounded-xl border border-amber/25 bg-[#FFF7EB] p-4 text-xs leading-5 text-muted"><strong class="text-wine">Private application data.</strong> Use this information only for authorised FIYFF review and communication. Document downloads are recorded in the audit log.</div>
            <dl class="mt-6 grid gap-4 rounded-xl bg-[#FAF6F4] p-5 text-sm sm:grid-cols-2">
                <div><dt class="font-bold text-wine">Email</dt><dd class="mt-1 break-all text-muted"><?= e($selected['email']) ?></dd></div>
                <div><dt class="font-bold text-wine">Phone</dt><dd class="mt-1 text-muted"><?= e($selected['phone']) ?></dd></div>
                <div><dt class="font-bold text-wine">Location</dt><dd class="mt-1 text-muted"><?= e($selected['location']) ?></dd></div>
                <div><dt class="font-bold text-wine">Consent</dt><dd class="mt-1 text-muted"><?= e(format_date($selected['consented_at'], 'M j, Y · g:i A')) ?></dd></div>
            </dl>

            <div class="mt-7 space-y-8">
                <?php foreach ($answerSections as $sectionTitle => $sectionFields): ?>
                    <section><h3 class="border-b border-line pb-3 font-display text-2xl text-wine"><?= e($sectionTitle) ?></h3><dl class="mt-5 grid gap-5 sm:grid-cols-2">
                        <?php foreach ($sectionFields as $field):
                            $value = $answers[$field['key']] ?? '';
                            if (is_array($value)) {
                                $value = implode(', ', $value);
                            }
                        ?><div class="<?= ($field['type'] ?? '') === 'textarea' ? 'sm:col-span-2' : '' ?>"><dt class="text-xs font-bold uppercase tracking-[.08em] text-muted"><?= e($field['label'] ?? $field['key']) ?></dt><dd class="mt-2 whitespace-pre-wrap break-words leading-7 text-muted"><?= e($value !== '' ? $value : 'Not provided') ?></dd></div><?php endforeach; ?>
                    </dl></section>
                <?php endforeach; ?>
            </div>

            <?php if ($documents): ?>
                <section class="mt-8"><h3 class="border-b border-line pb-3 font-display text-2xl text-wine">Protected documents</h3><div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <?php foreach ($documents as $document): ?><a class="admin-list-card" href="<?= e(url('/admin/grant-documents/' . $document['id'])) ?>"><span class="grid size-10 shrink-0 place-items-center rounded-full bg-blush text-wine">↓</span><span class="min-w-0"><strong class="block truncate text-sm text-wine"><?= e($documentLabels[$document['field_key']] ?? $document['field_key']) ?></strong><span class="block truncate text-xs text-muted"><?= e($document['original_name']) ?> · <?= e(number_format((int) $document['size_bytes'] / 1024, 1)) ?> KB</span></span></a><?php endforeach; ?>
                </div></section>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/admin/grant-applications')) ?>" class="mt-8 space-y-5" data-unsaved-form>
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                <div class="form-grid">
                    <div class="form-field"><label>Status</label><select class="form-control" name="status"><?php foreach (['submitted','in_review','shortlisted','declined','awarded'] as $status): ?><option value="<?= e($status) ?>" <?= $selected['status'] === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label>Assigned reviewer</label><select class="form-control" name="assigned_reviewer"><option value="">Unassigned</option><?php foreach ($admins as $admin): ?><option value="<?= (int) $admin['id'] ?>" <?= (int) $selected['assigned_reviewer'] === (int) $admin['id'] ? 'selected' : '' ?>><?= e($admin['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field md:col-span-2"><label>Internal review notes</label><textarea class="form-control min-h-32" name="internal_notes"><?= e($selected['internal_notes']) ?></textarea><p class="field-help">Internal only. Do not duplicate unnecessary medical details here.</p></div>
                </div>
                <button class="button button-primary">Save review</button>
            </form>
        <?php else: ?>
            <div class="empty-state">Select an application to review it.</div>
        <?php endif; ?>
    </section>
</div>
