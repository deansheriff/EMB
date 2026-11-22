<header class="admin-page-header">
    <div><p class="text-sm text-muted">Transactional delivery history</p><h1>Email log</h1></div>
    <nav class="flex flex-wrap gap-2" aria-label="Email status filters"><?php foreach (['all'=>'All','sent'=>'Sent','failed'=>'Failed','skipped'=>'Skipped'] as $value => $label): ?><a class="button <?= $status === $value ? 'button-primary' : 'button-secondary' ?>" href="<?= e(url('/admin/email-log?status=' . $value)) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
</header>

<?php if ($canSendTest): ?>
<section class="admin-card mb-6">
    <div class="admin-card-header"><h2>Send a test email</h2><span class="text-xs text-muted">Uses the SMTP settings saved in Site settings</span></div>
    <?php if ($smtpProblems): ?>
        <div class="mt-4 rounded-xl border border-amber bg-blush/40 p-4 text-sm text-ink" role="alert">
            <p class="font-bold">Email cannot be sent until these are fixed in <a class="text-wine underline" href="<?= e(url('/admin/settings')) ?>">Site settings</a>:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5"><?php foreach ($smtpProblems as $problem): ?><li><?= e($problem) ?></li><?php endforeach; ?></ul>
        </div>
    <?php else: ?>
        <p class="mt-3 text-sm text-muted">Sending through <strong><?= e((string) setting('smtp_host')) ?></strong> as <strong><?= e((string) setting('smtp_from_email')) ?></strong>. If the send fails, the exact reason is shown here and in the log below.</p>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/admin/email-test')) ?>" class="mt-5 flex flex-wrap items-end gap-3">
        <?= csrf_field() ?>
        <div class="form-field min-w-[16rem] flex-1">
            <label for="test-recipient">Send the test to</label>
            <input id="test-recipient" class="form-control" type="email" name="recipient" value="<?= e($testRecipient) ?>" required autocomplete="email">
        </div>
        <button class="button button-primary">Send test email</button>
    </form>
</section>
<?php endif; ?>

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
