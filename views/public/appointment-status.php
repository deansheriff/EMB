<?php
$paid = $appointment['payment_status'] === 'paid';
$pending = $appointment['payment_status'] === 'pending';
$failed = $appointment['payment_status'] === 'failed';
$scheduled = $appointment['status'] === 'scheduled' && $appointment['scheduled_at'];
?>
<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">Appointment <?= e($appointment['booking_code']) ?></p>
        <h1 class="page-title mt-5"><?= $paid ? 'Payment confirmed. Your request is ready.' : ($appointment['payment_status'] === 'not_required' ? 'Your appointment request is received.' : 'Complete your booking securely.') ?></h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Keep this private booking reference for future communication with Emb Chronicles.</p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto grid max-w-content gap-8 px-5 lg:grid-cols-[1fr_.7fr] lg:px-6">
        <div class="rounded-[28px] border border-line bg-white p-6 shadow-soft sm:p-9">
            <div class="flex flex-wrap items-center gap-3 border-b border-line pb-6">
                <span class="status status-<?= e($appointment['status']) ?>"><?= e(str_replace('_', ' ', $appointment['status'])) ?></span>
                <span class="status <?= $paid ? 'status-completed' : ($failed ? 'status-cancelled' : 'status-scheduled') ?>">Payment: <?= e(str_replace('_', ' ', $appointment['payment_status'])) ?></span>
            </div>
            <dl class="mt-7 grid gap-5 text-sm sm:grid-cols-2">
                <div><dt class="font-bold text-wine">Booking reference</dt><dd class="mt-1 text-muted"><?= e($appointment['booking_code']) ?></dd></div>
                <div><dt class="font-bold text-wine">Session</dt><dd class="mt-1 text-muted"><?= e($appointment['consultation_type']) ?></dd></div>
                <div><dt class="font-bold text-wine">Preferred date</dt><dd class="mt-1 text-muted"><?= e($appointment['preferred_date'] ? format_date($appointment['preferred_date']) : 'Flexible') ?></dd></div>
                <div><dt class="font-bold text-wine">Preferred time</dt><dd class="mt-1 text-muted"><?= e($appointment['preferred_time'] ? substr($appointment['preferred_time'], 0, 5) : 'Flexible') ?></dd></div>
                <?php if ((int) $appointment['amount_due'] > 0): ?><div><dt class="font-bold text-wine">Amount</dt><dd class="mt-1 text-muted"><?= e(format_money((int) $appointment['amount_due'], $appointment['currency'])) ?></dd></div><?php endif; ?>
                <?php if ($scheduled): ?><div><dt class="font-bold text-wine">Confirmed schedule</dt><dd class="mt-1 text-muted"><?= e(format_date($appointment['scheduled_at'], 'M j, Y · g:i A')) ?></dd></div><?php endif; ?>
            </dl>

            <?php if (!$paid && (int) $appointment['amount_due'] > 0): ?>
                <div class="mt-8 rounded-2xl border border-amber/30 bg-[#FFF7EB] p-5">
                    <p class="font-bold text-wine"><?= $pending ? 'Payment is still pending.' : 'Your payment is not complete.' ?></p>
                    <p class="mt-2 text-sm leading-6 text-muted">Use the button below to open a new secure Paystack checkout. If you have already paid, wait a moment and refresh before trying again.</p>
                    <form method="post" action="<?= e(url('/appointment/pay/' . $appointment['booking_code'])) ?>" class="mt-5">
                        <?= csrf_field() ?>
                        <button class="button button-primary">Pay <?= e(format_money((int) $appointment['amount_due'], $appointment['currency'])) ?> securely</button>
                    </form>
                </div>
            <?php elseif ($paid): ?>
                <div class="success-panel mt-8 text-left">
                    <span class="text-3xl">✓</span>
                    <h2 class="mt-3 font-display text-3xl text-wine">Payment verified</h2>
                    <p class="mt-2 text-muted">We will contact you to confirm the final date and time. Your confirmation email is sent when SMTP is configured.</p>
                </div>
            <?php endif; ?>
        </div>

        <aside class="space-y-5">
            <div class="rounded-[28px] bg-blush p-8">
                <p class="eyebrow">What happens next</p>
                <ol class="mt-5 space-y-4 text-sm leading-6 text-muted">
                    <li><strong class="text-wine">1.</strong> Payment is verified against Paystack’s server records.</li>
                    <li><strong class="text-wine">2.</strong> The team reviews your preferred timing.</li>
                    <li><strong class="text-wine">3.</strong> You receive the confirmed schedule using your preferred contact method.</li>
                </ol>
            </div>
            <div class="rounded-2xl border border-line bg-white p-6">
                <p class="font-bold text-wine">Need help?</p>
                <p class="mt-3 text-sm leading-6 text-muted">Contact us with your booking reference if checkout was interrupted or you need to change your request.</p>
                <a class="button button-secondary mt-5" href="<?= e(url('/contact')) ?>">Contact support</a>
            </div>
        </aside>
    </div>
</section>
