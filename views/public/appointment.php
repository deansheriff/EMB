<?php
$selectedAppointmentType = (string) old('appointment_type_id');
if ($selectedAppointmentType === '' && !empty($_GET['service'])) {
    foreach ($appointmentTypes as $candidateType) {
        if (strcasecmp((string) $candidateType['name'], trim((string) $_GET['service'])) === 0) {
            $selectedAppointmentType = (string) $candidateType['id'];
            break;
        }
    }
}
?>
<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">Book a session</p>
        <h1 class="page-title mt-5">Let’s make your next step clearer.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Tell us what kind of support you need and choose an available session time.<?php if ($paymentsEnabled): ?> Paid sessions continue to secure Paystack checkout.<?php endif; ?></p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto grid max-w-content gap-8 px-5 lg:grid-cols-[1.15fr_.85fr] lg:px-6">
        <div class="rounded-[28px] border border-line bg-white p-6 shadow-soft sm:p-9">
            <?php if (isset($_GET['sent'])): ?>
                <div class="success-panel"><span class="text-3xl">✓</span><h2 class="mt-4 font-display text-3xl text-wine">Request received</h2><p class="mt-3 text-muted">We will use the contact details you provided to confirm a date and time.</p><a class="button button-secondary mt-6" href="<?= e(url('/services')) ?>">Explore services</a></div>
            <?php elseif (!$availability['enabled'] || !$availability['slots'] || !$appointmentTypes): ?>
                <div class="empty-state"><h2 class="font-display text-3xl text-wine">Online booking is temporarily paused</h2><p class="mt-3 text-muted">Please contact the team and we will help you arrange a suitable time.</p><a class="button button-primary mt-6" href="<?= e(url('/contact')) ?>">Contact the team</a></div>
            <?php else: ?>
            <form method="post" action="<?= e(url('/appointment')) ?>" class="space-y-9">
                <?= csrf_field() ?>
                <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <fieldset><legend class="form-section-title"><span>01</span> Choose your session</legend><div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <?php foreach ($appointmentTypes as $appointmentType): ?>
                        <?php $priceLabel = (int) $appointmentType['price'] > 0 ? format_money((int) $appointmentType['price'], $appointmentType['currency']) : 'No fee'; ?>
                        <label class="choice-card">
                            <input type="radio" name="appointment_type_id" value="<?= (int) $appointmentType['id'] ?>" data-appointment-type-option data-price="<?= (int) $appointmentType['price'] ?>" data-price-label="<?= e($priceLabel) ?>" <?= $selectedAppointmentType === (string) $appointmentType['id'] ? 'checked' : '' ?> required>
                            <span><strong class="block"><?= e($appointmentType['name']) ?></strong><span class="mt-1 block text-xs font-bold text-berry"><?= e($priceLabel) ?></span><?php if ($appointmentType['description']): ?><span class="mt-2 block text-xs font-normal leading-5 text-muted"><?= e($appointmentType['description']) ?></span><?php endif; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div></fieldset>
                <fieldset data-appointment-availability data-slots="<?= e(json_encode($availability['slots'], JSON_THROW_ON_ERROR)) ?>" data-blocked-dates="<?= e(json_encode($availability['blocked_dates'], JSON_THROW_ON_ERROR)) ?>" data-bookings="<?= e(json_encode($availability['bookings'], JSON_THROW_ON_ERROR)) ?>" data-daily-limit="<?= (int) $availability['daily_limit'] ?>" data-minimum-timestamp="<?= e($availability['minimum_timestamp']) ?>" data-old-slot="<?= e(old('availability_slot_id')) ?>">
                    <legend class="form-section-title"><span>02</span> Choose an available time</legend>
                    <div class="mt-5 form-grid">
                        <div class="form-field"><label for="preferred-date">Appointment date <span>*</span></label><input class="form-control" type="date" id="preferred-date" name="preferred_date" value="<?= e(old('preferred_date')) ?>" min="<?= e($availability['min_date']) ?>" max="<?= e($availability['max_date']) ?>" required><p class="field-help">Bookings are open up to <?= (int) $availability['window_days'] ?> days ahead with at least <?= (int) $availability['notice_hours'] ?> hours' notice.</p></div>
                        <div class="form-field"><label for="availability-slot">Available time <span>*</span></label><select class="form-control" id="availability-slot" name="availability_slot_id" required disabled><option value="">Choose a date first</option></select><p class="field-help" data-availability-message aria-live="polite">Select a date to view its available time slots.</p></div>
                    </div>
                </fieldset>
                <fieldset><legend class="form-section-title"><span>03</span> Your details</legend><div class="mt-5 form-grid">
                    <div class="form-field"><label for="appointment-name">Full name <span>*</span></label><input class="form-control" id="appointment-name" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"></div>
                    <div class="form-field"><label for="appointment-email">Email <span>*</span></label><input class="form-control" type="email" id="appointment-email" name="email" value="<?= e(old('email')) ?>" required autocomplete="email"></div>
                    <div class="form-field"><label for="appointment-phone">Phone / WhatsApp <span>*</span></label><input class="form-control" id="appointment-phone" name="phone" value="<?= e(old('phone')) ?>" required autocomplete="tel"></div>
                    <div class="form-field"><label for="preferred-contact">Preferred contact method <span>*</span></label><select class="form-control" id="preferred-contact" name="preferred_contact" required><option value="">Choose one</option><option <?= old('preferred_contact') === 'WhatsApp' ? 'selected' : '' ?>>WhatsApp</option><option <?= old('preferred_contact') === 'Email' ? 'selected' : '' ?>>Email</option><option <?= old('preferred_contact') === 'Phone call' ? 'selected' : '' ?>>Phone call</option></select></div>
                    <div class="form-field md:col-span-2"><label for="appointment-message">What would you like support with?</label><textarea class="form-control min-h-36" id="appointment-message" name="message"><?= e(old('message')) ?></textarea></div>
                </div></fieldset>
                <label class="consent-row"><input type="checkbox" name="consent" value="1" required><span>I consent to the storage and use of this information to arrange my requested session.</span></label>
                <button class="button button-primary" data-appointment-submit data-payments-enabled="<?= $paymentsEnabled ? '1' : '0' ?>">Choose a session to continue</button>
                <?php if ($paymentsEnabled): ?><p class="text-xs leading-5 text-muted">Payment for paid sessions is processed securely by Paystack. EMB Chronicles does not receive or store your card or bank credentials.</p><?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
        <aside class="space-y-5">
            <div class="rounded-[28px] bg-wine p-8 text-white"><p class="text-xs font-bold uppercase tracking-[.15em] text-white/65">Selected session fee</p><p class="mt-3 font-display text-4xl" data-appointment-price>Choose a session</p><p class="mt-3 text-sm leading-6 text-white/70" data-appointment-payment-note>Select an appointment type to see its fee and payment step.</p></div>
            <div class="rounded-[28px] bg-blush p-8"><p class="eyebrow">What to expect</p><h2 class="mt-4 font-display text-3xl text-wine">A focused, judgment-free conversation</h2><ul class="mt-6 space-y-4"><?php foreach (['A review of the questions you bring','Clear language for clinical and laboratory concepts','A practical list of next conversations or preparation steps'] as $item): ?><li class="flex gap-3 leading-7 text-muted"><span class="check">✓</span><?= e($item) ?></li><?php endforeach; ?></ul></div>
            <div class="rounded-2xl border border-line bg-white p-6"><p class="font-bold text-wine">What to prepare</p><p class="mt-3 text-sm leading-6 text-muted">Bring the questions, reports, timelines, or career background you would like to discuss. Do not upload sensitive records through this form.</p></div>
            <div class="rounded-2xl border border-line bg-white p-6"><p class="font-bold text-wine">Important care note</p><p class="mt-3 text-sm leading-6 text-muted">Sessions provide education and guidance. They are not emergency care and do not replace a licensed clinician.</p></div>
        </aside>
    </div>
</section>
