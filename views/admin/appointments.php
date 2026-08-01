<header class="admin-page-header">
    <div><p class="text-sm text-muted">Booking, payment, and scheduling</p><h1>Appointments</h1></div>
    <nav class="flex flex-wrap gap-2" aria-label="Appointment tools">
        <a class="button button-secondary" href="<?= e(url('/admin/availability')) ?>">Manage availability</a>
        <?php foreach (['all'=>'All','paid'=>'Paid','pending'=>'Pending payment','failed'=>'Failed','not_required'=>'No payment'] as $value => $label): ?>
            <a class="button <?= $paymentFilter === $value ? 'button-primary' : 'button-secondary' ?>" href="<?= e(url('/admin/appointments?payment=' . $value)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
</header>

<div class="admin-inbox">
    <section class="admin-card p-0">
        <div class="divide-y divide-line">
            <?php foreach ($appointments as $item): ?>
                <?php $paymentTone = $item['payment_status'] === 'paid' ? 'completed' : ($item['payment_status'] === 'failed' ? 'cancelled' : ($item['payment_status'] === 'pending' ? 'scheduled' : 'draft')); ?>
                <a class="block p-5 hover:bg-blush/30 <?= $selected && (int) $selected['id'] === (int) $item['id'] ? 'bg-blush/50' : '' ?>" href="<?= e(url('/admin/appointments?view=' . $item['id'] . '&payment=' . $paymentFilter)) ?>">
                    <div class="flex items-center gap-2"><p class="font-semibold text-wine"><?= e($item['name']) ?></p><span class="status status-<?= e($item['status']) ?> ml-auto"><?= e(str_replace('_', ' ', $item['status'])) ?></span></div>
                    <p class="mt-2 text-sm text-muted"><?= e($item['consultation_type']) ?></p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-muted"><span><?= e($item['booking_code']) ?></span><span>·</span><span class="status status-<?= e($paymentTone) ?>"><?= e(str_replace('_', ' ', $item['payment_status'])) ?></span></div>
                </a>
            <?php endforeach; ?>
            <?php if (!$appointments): ?><div class="empty-state m-5">No appointments match this payment filter.</div><?php endif; ?>
        </div>
    </section>

    <section class="admin-card">
        <?php if ($selected): ?>
            <?php $paymentTone = $selected['payment_status'] === 'paid' ? 'completed' : ($selected['payment_status'] === 'failed' ? 'cancelled' : ($selected['payment_status'] === 'pending' ? 'scheduled' : 'draft')); ?>
            <div class="admin-card-header">
                <div><p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= e($selected['booking_code']) ?></p><h2 class="mt-1"><?= e($selected['name']) ?></h2></div>
                <div class="text-right"><span class="status status-<?= e($paymentTone) ?>"><?= e(str_replace('_', ' ', $selected['payment_status'])) ?></span><time class="mt-2 block text-xs text-muted"><?= e(format_date($selected['created_at'], 'M j, Y · g:i A')) ?></time></div>
            </div>

            <dl class="mt-6 grid gap-4 rounded-xl bg-[#FAF6F4] p-5 text-sm sm:grid-cols-2">
                <div><dt class="font-bold text-wine">Email</dt><dd class="mt-1 text-muted"><?= e($selected['email']) ?></dd></div>
                <div><dt class="font-bold text-wine">Phone</dt><dd class="mt-1 text-muted"><?= e($selected['phone']) ?></dd></div>
                <div><dt class="font-bold text-wine">Preferred date</dt><dd class="mt-1 text-muted"><?= e($selected['preferred_date'] ? format_date($selected['preferred_date']) : 'Flexible') ?><?= $selected['preferred_time'] ? ' · ' . e(substr($selected['preferred_time'], 0, 5)) : '' ?></dd></div>
                <div><dt class="font-bold text-wine">Contact via</dt><dd class="mt-1 text-muted"><?= e($selected['preferred_contact']) ?></dd></div>
                <div><dt class="font-bold text-wine">Amount due</dt><dd class="mt-1 text-muted"><?= e(format_money((int) $selected['amount_due'], $selected['currency'])) ?></dd></div>
                <div><dt class="font-bold text-wine">Paid at</dt><dd class="mt-1 text-muted"><?= e($selected['paid_at'] ? format_date($selected['paid_at'], 'M j, Y · g:i A') : 'Not paid') ?></dd></div>
                <div class="sm:col-span-2"><dt class="font-bold text-wine">Paystack reference</dt><dd class="mt-1 break-all text-muted"><?= e($selected['payment_reference'] ?: 'None') ?></dd></div>
            </dl>

            <div class="mt-7"><p class="text-xs font-bold uppercase tracking-[.12em] text-muted">Client message</p><p class="mt-2 whitespace-pre-wrap leading-8 text-muted"><?= e($selected['message'] ?: 'No additional message.') ?></p></div>

            <form method="post" action="<?= e(url('/admin/appointments')) ?>" class="mt-8 space-y-5" data-unsaved-form>
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $selected['id'] ?>">
                <div class="form-grid">
                    <div class="form-field"><label>Status</label><select class="form-control" name="status"><?php foreach (['pending_payment','new','contacted','scheduled','completed','cancelled'] as $status): ?><option value="<?= e($status) ?>" <?= $selected['status'] === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label>Confirmed schedule</label><input class="form-control" type="datetime-local" name="scheduled_at" value="<?= e($selected['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($selected['scheduled_at'])) : '') ?>"></div>
                    <div class="form-field md:col-span-2"><label>Internal notes</label><textarea class="form-control min-h-28" name="admin_notes"><?= e($selected['admin_notes']) ?></textarea><p class="field-help">Visible only to administrators.</p></div>
                </div>
                <label class="consent-row"><input type="checkbox" name="send_confirmation" value="1"><span>Email this status and confirmed schedule to the client</span></label>
                <button class="button button-primary">Save appointment</button>
            </form>

            <div class="mt-9 border-t border-line pt-7">
                <div class="admin-card-header"><h2>Payment attempts</h2><span class="text-xs text-muted"><?= count($payments) ?> record<?= count($payments) === 1 ? '' : 's' ?></span></div>
                <?php if ($payments): ?><div class="admin-table-wrap mt-4"><table class="admin-table"><thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Channel</th><th>Created</th></tr></thead><tbody><?php foreach ($payments as $payment): ?><tr><td class="max-w-52 break-all"><?= e($payment['reference']) ?></td><td><?= e(format_money((int) $payment['amount'], $payment['currency'])) ?></td><td><?= e($payment['status']) ?></td><td><?= e($payment['channel'] ?: '—') ?></td><td><?= e(format_date($payment['created_at'], 'M j · g:i A')) ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-state mt-5">No Paystack attempts for this appointment.</div><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">Select an appointment request.</div>
        <?php endif; ?>
    </section>
</div>
