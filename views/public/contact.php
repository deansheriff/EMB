<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">Contact Emb Chronicles</p>
        <h1 class="page-title mt-5">You don’t have to figure it out alone.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Send a message for general questions, consultation guidance, community information, or FIYFF enquiries.</p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto grid max-w-content gap-8 px-5 lg:grid-cols-[1.15fr_.85fr] lg:px-6">
        <div class="rounded-[28px] border border-line bg-white p-6 shadow-soft sm:p-9">
            <?php if (isset($_GET['sent'])): ?>
                <div class="success-panel"><span class="text-3xl">✓</span><h2 class="mt-4 font-display text-3xl text-wine">Message received</h2><p class="mt-3 text-muted">Thank you for reaching out. Keep an eye on the email or phone number you provided.</p></div>
            <?php else: ?>
            <form method="post" action="<?= e(url('/contact')) ?>" class="form-grid">
                <?= csrf_field() ?>
                <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="form-field"><label for="contact-name">Full name <span>*</span></label><input class="form-control" id="contact-name" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"></div>
                <div class="form-field"><label for="contact-email">Email <span>*</span></label><input class="form-control" id="contact-email" name="email" value="<?= e(old('email')) ?>" type="email" required autocomplete="email"></div>
                <div class="form-field"><label for="contact-phone">Phone / WhatsApp</label><input class="form-control" id="contact-phone" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel"></div>
                <div class="form-field"><label for="contact-topic">What can we help with?</label><select class="form-control" id="contact-topic" name="topic"><option value="">Select a topic</option><?php foreach (['Fertility consultation','Events','FIYFF Foundation','STEM Community','TTC Community','Other'] as $item): ?><option <?= old('topic') === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></div>
                <div class="form-field md:col-span-2"><label for="contact-message">Message <span>*</span></label><textarea class="form-control min-h-40" id="contact-message" name="message" required minlength="15"><?= e(old('message')) ?></textarea><p class="field-help">Please do not submit urgent medical information. For an emergency, contact local emergency services or your licensed medical team.</p></div>
                <label class="consent-row md:col-span-2"><input type="checkbox" name="consent" value="1" required><span>I agree that my submitted information may be stored and used to respond to this enquiry.</span></label>
                <div class="md:col-span-2"><button class="button button-primary">Send message</button></div>
            </form>
            <?php endif; ?>
        </div>
        <aside class="space-y-5">
            <div class="rounded-[28px] bg-wine p-8 text-white">
                <p class="font-display text-3xl">Prefer a direct conversation?</p>
                <p class="mt-4 leading-7 text-white/70">Start a WhatsApp chat using the business number already trusted by the community.</p>
                <a class="button mt-6 bg-white text-wine" href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
            </div>
            <?php foreach ([
                ['Visit', setting('address')],
                ['Call', setting('phone')],
                ['Email', setting('email')],
                ['Opening hours', setting('opening_hours')],
            ] as [$label, $value]): ?><div class="rounded-2xl border border-line bg-white p-6"><p class="text-xs font-bold uppercase tracking-[.14em] text-muted"><?= e($label) ?></p><p class="mt-3 font-semibold leading-7 text-wine"><?= e($value) ?></p></div><?php endforeach; ?>
        </aside>
    </div>
</section>

