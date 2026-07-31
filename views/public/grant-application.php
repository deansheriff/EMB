<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-16 lg:px-6 lg:py-24">
        <nav class="breadcrumb"><a href="<?= e(url('/events/' . $event['slug'])) ?>"><?= e($event['title']) ?></a><span>/</span><span>Application</span></nav>
        <span class="chip mt-7">Secure grant application</span>
        <h1 class="page-title mt-5">Apply for the FIYFF Fertility Support Grant</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Complete each section carefully. Eligibility allows an application to be reviewed; it does not guarantee selection.</p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto max-w-4xl px-5 lg:px-6">
        <div class="rounded-[28px] border border-line bg-white p-6 shadow-soft sm:p-10">
            <?php if (isset($_GET['sent'])): ?>
                <div class="success-panel"><span class="text-3xl">✓</span><h2 class="mt-4 font-display text-3xl text-wine">Application submitted</h2><p class="mt-3 text-muted">Keep your reference code: <strong><?= e($_SESSION['grant_code'] ?? '') ?></strong>. The FIYFF team will use your submitted contact details for any follow-up.</p></div>
            <?php else: ?>
            <form method="post" action="<?= e(url('/grant-application/' . $event['slug'])) ?>" class="space-y-9">
                <?= csrf_field() ?>
                <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <fieldset><legend class="form-section-title"><span>01</span> Applicant information</legend><div class="mt-5 form-grid">
                    <div class="form-field"><label for="grant-name">Full name <span>*</span></label><input class="form-control" id="grant-name" name="full_name" value="<?= e(old('full_name')) ?>" required></div>
                    <div class="form-field"><label for="grant-email">Email <span>*</span></label><input class="form-control" type="email" id="grant-email" name="email" value="<?= e(old('email')) ?>" required></div>
                    <div class="form-field"><label for="grant-phone">Phone / WhatsApp <span>*</span></label><input class="form-control" id="grant-phone" name="phone" value="<?= e(old('phone')) ?>" required></div>
                    <div class="form-field"><label for="grant-location">City / State <span>*</span></label><input class="form-control" id="grant-location" name="location" value="<?= e(old('location')) ?>" required></div>
                </div></fieldset>
                <fieldset><legend class="form-section-title"><span>02</span> Your journey</legend><div class="mt-5 space-y-5">
                    <div class="form-field"><label for="journey-summary">Please describe your fertility journey so far <span>*</span></label><textarea class="form-control min-h-40" id="journey-summary" name="journey_summary" required minlength="30"><?= e(old('journey_summary')) ?></textarea></div>
                    <div class="form-field"><label for="support-need">How would this support help? <span>*</span></label><textarea class="form-control min-h-40" id="support-need" name="support_need" required><?= e(old('support_need')) ?></textarea></div>
                    <div class="form-field"><label for="clinic-status">Current clinic or consultation status</label><select class="form-control" id="clinic-status" name="clinic_status"><option value="">Choose one</option><?php foreach (['Not yet selected a clinic','Initial consultation completed','Tests in progress','Treatment plan received','Other'] as $item): ?><option <?= old('clinic_status') === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?></select></div>
                </div></fieldset>
                <fieldset><legend class="form-section-title"><span>03</span> Declarations</legend><div class="mt-5 space-y-4">
                    <label class="consent-row"><input type="checkbox" name="eligibility" value="1" required><span>I have reviewed the published eligibility information and confirm that this application is complete and truthful to the best of my knowledge.</span></label>
                    <label class="consent-row"><input type="checkbox" name="consent" value="1" required><span>I consent to the secure storage and review of this information for administration of the FIYFF grant.</span></label>
                </div></fieldset>
                <button class="button button-primary">Submit application</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

