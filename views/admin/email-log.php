<header class="admin-page-header">
    <div><p class="text-sm text-muted">Transactional delivery history</p><h1>Email log</h1></div>
    <nav class="flex flex-wrap gap-2" aria-label="Email status filters"><?php foreach (['all'=>'All','sent'=>'Sent','failed'=>'Failed','skipped'=>'Skipped'] as $value => $label): ?><a class="button <?= $status === $value ? 'button-primary' : 'button-secondary' ?>" href="<?= e(url('/admin/email-log?status=' . $value)) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
</header>

<section class="admin-card">
    <div class="admin-card-header"><h2>Latest messages</h2><span class="text-xs text-muted">Most recent 200</span></div>
    <?php if ($messages): ?>
        <div class="admin-table-wrap mt-4">
            <table class="admin-table">
                <thead><tr><th>Recipient</th><th>Subject</th><th>Template</th><th>Status</th><th>Details</th><th>Time</th></tr></thead>
                <tbody><?php foreach ($messages as $message): ?><tr><td><?= e($message['recipient']) ?></td><td><?= e($message['subject']) ?></td><td><?= e(str_replace('_', ' ', $message['template_key'])) ?></td><td><span class="status status-<?= e($message['status'] === 'sent' ? 'completed' : ($message['status'] === 'failed' ? 'cancelled' : 'draft')) ?>"><?= e($message['status']) ?></span></td><td class="max-w-sm break-words"><?= e($message['error_message'] ?: '—') ?></td><td><?= e(format_date($message['created_at'], 'M j · g:i A')) ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    <?php else: ?><div class="empty-state mt-5">No email records match this filter.</div><?php endif; ?>
</section>
