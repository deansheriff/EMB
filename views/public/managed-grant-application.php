<?php
$sectionCount = count($sections);
$totalSteps = $sectionCount + 1;
$storageKey = 'embGrantDraft:' . $form['slug'];
?>
<section class="grant-app-shell" data-grant-application data-storage-key="<?= e($storageKey) ?>">
    <?php if (isset($_GET['sent'])): ?>
        <div class="mx-auto flex min-h-[72vh] max-w-3xl items-center px-5 py-16">
            <div class="w-full rounded-[28px] border border-line bg-white p-8 text-center shadow-soft sm:p-14">
                <div class="mx-auto grid size-20 place-items-center rounded-full bg-blush text-4xl text-wine" aria-hidden="true">✓</div>
                <p class="mt-7 text-xs font-bold uppercase tracking-[.14em] text-berry">Application complete</p>
                <h1 class="mt-3 font-display text-4xl text-wine sm:text-5xl">Thank you for applying.</h1>
                <p class="mx-auto mt-5 max-w-xl leading-8 text-muted"><?= e($form['success_message'] ?: 'Your application has been received and will be reviewed with care.') ?></p>
                <div class="mx-auto mt-7 max-w-md rounded-xl bg-ivory p-5 text-sm text-muted">
                    Your application reference is
                    <strong class="mt-1 block text-lg text-wine"><?= e($_SESSION['grant_code'] ?? '') ?></strong>
                </div>
                <p class="mt-6 text-sm text-muted">A confirmation email will be sent when email delivery is enabled.</p>
                <a class="button button-secondary mt-8" href="<?= e(url('/fiyff-foundation')) ?>">Return to FIYFF Foundation</a>
            </div>
        </div>
    <?php elseif (!$availability['accepting']): ?>
        <div class="mx-auto flex min-h-[68vh] max-w-3xl items-center px-5 py-16">
            <div class="w-full rounded-[28px] border border-line bg-white p-8 text-center shadow-soft sm:p-14">
                <span class="chip">Grant application</span>
                <h1 class="mt-5 font-display text-4xl text-wine sm:text-5xl"><?= e($form['title']) ?></h1>
                <p class="mx-auto mt-5 max-w-xl leading-8 text-muted"><?= e($availability['message']) ?></p>
                <a class="button button-secondary mt-8" href="<?= e(url('/fiyff-foundation')) ?>">View FIYFF Foundation</a>
            </div>
        </div>
    <?php else: ?>
        <section class="grant-welcome" data-grant-welcome>
            <div class="mx-auto grid min-h-[76vh] max-w-content items-center gap-10 px-5 py-14 lg:grid-cols-[1fr_420px] lg:px-6">
                <div class="max-w-2xl">
                    <span class="chip">Grant applications are open</span>
                    <h1 class="mt-6 font-display text-5xl leading-[1.02] tracking-[-.035em] text-wine sm:text-6xl"><?= e($form['title']) ?></h1>
                    <p class="mt-7 text-lg leading-8 text-muted"><?= e($form['intro']) ?></p>
                    <?php if ($form['eligibility_notice']): ?><p class="mt-5 rounded-xl border border-amber/25 bg-[#FFF7EB] p-4 text-sm leading-6 text-muted"><?= e($form['eligibility_notice']) ?></p><?php endif; ?>
                    <div class="mt-8 flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                        <button class="button button-primary" type="button" data-grant-start>Start application <span aria-hidden="true">→</span></button>
                        <?php if ((int) $form['allow_save_progress'] === 1): ?><span class="text-xs text-muted">Your typed answers can be saved on this device.</span><?php endif; ?>
                    </div>
                </div>
                <aside class="rounded-[28px] border border-line bg-white p-7 shadow-soft sm:p-8">
                    <p class="text-xs font-bold uppercase tracking-[.12em] text-berry">Application overview</p>
                    <h2 class="mt-2 font-display text-3xl text-wine"><?= $totalSteps ?> straightforward steps</h2>
                    <ol class="mt-7 space-y-5">
                        <?php foreach ($sections as $index => $section): ?>
                            <li class="flex gap-4"><span class="step-number"><?= $index + 1 ?></span><div><strong class="text-sm text-wine"><?= e($section['title']) ?></strong><p class="mt-1 text-xs leading-5 text-muted"><?= count($section['fields']) ?> application fields</p></div></li>
                        <?php endforeach; ?>
                        <li class="flex gap-4"><span class="step-number"><?= $totalSteps ?></span><div><strong class="text-sm text-wine">Review and consent</strong><p class="mt-1 text-xs leading-5 text-muted">Check your answers before submitting.</p></div></li>
                    </ol>
                    <div class="mt-7 rounded-xl bg-ivory p-4 text-xs leading-5 text-muted"><strong class="text-wine">Private and confidential.</strong> Uploaded documents are available only to authorised grant reviewers.</div>
                </aside>
            </div>
        </section>

        <section class="grant-form-view" data-grant-form-view hidden>
            <aside class="grant-step-sidebar">
                <a class="font-display text-2xl text-white" href="<?= e(url('/')) ?>"><?= e(setting('site_name', 'Emb Chronicles')) ?></a>
                <p class="mt-2 text-xs text-white/60" data-grant-step-count>Step 1 of <?= $totalSteps ?></p>
                <nav class="mt-8 space-y-2" aria-label="Application progress">
                    <?php foreach ($sections as $index => $section): ?>
                        <button type="button" class="grant-step-link <?= $index === 0 ? 'is-active' : '' ?>" data-grant-nav="<?= $index + 1 ?>" <?= $index > 0 ? 'disabled' : '' ?>><span><?= $index + 1 ?></span><?= e($section['title']) ?></button>
                    <?php endforeach; ?>
                    <button type="button" class="grant-step-link" data-grant-nav="<?= $totalSteps ?>" disabled><span><?= $totalSteps ?></span>Review &amp; consent</button>
                </nav>
                <?php if ((int) $form['allow_save_progress'] === 1): ?><button class="mt-auto w-full rounded-xl border border-white/25 px-4 py-3 text-sm font-bold text-white hover:bg-white/10" type="button" data-grant-save>Save &amp; exit</button><?php endif; ?>
            </aside>

            <div class="grant-form-main">
                <header class="grant-mobile-progress">
                    <div><strong class="font-display text-xl text-wine">Grant application</strong><p class="text-xs text-muted" data-grant-step-count>Step 1 of <?= $totalSteps ?></p></div>
                    <?php if ((int) $form['allow_save_progress'] === 1): ?><button class="text-xs font-bold text-berry" type="button" data-grant-save>Save &amp; exit</button><?php endif; ?>
                </header>
                <form method="post" action="<?= e(url('/grants/' . $form['slug'] . '/apply')) ?>" enctype="multipart/form-data" class="mx-auto w-full max-w-4xl px-5 py-10 lg:px-10 lg:py-14" data-grant-form novalidate>
                    <?= csrf_field() ?>
                    <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <?php foreach ($sections as $sectionIndex => $section): ?>
                        <section class="grant-step-panel <?= $sectionIndex === 0 ? 'is-active' : '' ?>" data-grant-step="<?= $sectionIndex + 1 ?>">
                            <p class="text-xs font-bold uppercase tracking-[.14em] text-berry">Step <?= $sectionIndex + 1 ?> of <?= $totalSteps ?></p>
                            <h2 class="mt-3 font-display text-4xl tracking-[-.025em] text-wine sm:text-5xl"><?= e($section['title']) ?></h2>
                            <div class="mt-8 grid gap-5 rounded-[28px] border border-line bg-white p-6 shadow-soft md:grid-cols-6 sm:p-9">
                                <?php foreach ($section['fields'] as $field):
                                    $options = json_decode((string) ($field['options_json'] ?? ''), true) ?: [];
                                    $validation = json_decode((string) ($field['validation_json'] ?? ''), true) ?: [];
                                    $columnClass = match ($field['width']) { 'third' => 'md:col-span-2', 'half' => 'md:col-span-3', default => 'md:col-span-6' };
                                    $required = (int) $field['is_required'] === 1;
                                    $oldValue = (string) old($field['field_key']);
                                ?>
                                    <div class="form-field <?= $columnClass ?>" data-grant-field data-label="<?= e($field['label']) ?>" data-type="<?= e($field['field_type']) ?>">
                                        <label for="grant-<?= e($field['field_key']) ?>"><?= e($field['label']) ?><?= $required ? ' <span>*</span>' : '' ?></label>
                                        <?php if ($field['field_type'] === 'textarea'): ?>
                                            <textarea class="form-control min-h-40" id="grant-<?= e($field['field_key']) ?>" name="<?= e($field['field_key']) ?>" placeholder="<?= e($field['placeholder']) ?>" <?= $required ? 'required' : '' ?> <?= isset($validation['minlength']) ? 'minlength="' . (int) $validation['minlength'] . '"' : '' ?> <?= isset($validation['maxlength']) ? 'maxlength="' . (int) $validation['maxlength'] . '"' : '' ?>><?= e($oldValue) ?></textarea>
                                        <?php elseif ($field['field_type'] === 'select'): ?>
                                            <select class="form-control" id="grant-<?= e($field['field_key']) ?>" name="<?= e($field['field_key']) ?>" <?= $required ? 'required' : '' ?>>
                                                <option value="">Choose one</option>
                                                <?php foreach ($options as $option): ?><option value="<?= e($option) ?>" <?= $oldValue === (string) $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?>
                                            </select>
                                        <?php elseif ($field['field_type'] === 'radio'): ?>
                                            <div class="grid gap-3 sm:grid-cols-2" id="grant-<?= e($field['field_key']) ?>">
                                                <?php foreach ($options as $option): ?><label class="choice-card"><input type="radio" name="<?= e($field['field_key']) ?>" value="<?= e($option) ?>" <?= $oldValue === (string) $option ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>><?= e($option) ?></label><?php endforeach; ?>
                                            </div>
                                        <?php elseif ($field['field_type'] === 'checkbox'): ?>
                                            <label class="consent-row"><input id="grant-<?= e($field['field_key']) ?>" type="checkbox" name="<?= e($field['field_key']) ?>" value="1" <?= $oldValue ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>><span><?= e($field['help_text'] ?: $field['label']) ?></span></label>
                                        <?php elseif ($field['field_type'] === 'file'): ?>
                                            <input class="file-control" id="grant-<?= e($field['field_key']) ?>" type="file" name="<?= e($field['field_key']) ?>" accept=".jpg,.jpeg,.png,.pdf" <?= $required ? 'required' : '' ?>>
                                        <?php else: ?>
                                            <input class="form-control" id="grant-<?= e($field['field_key']) ?>" type="<?= e($field['field_type']) ?>" name="<?= e($field['field_key']) ?>" value="<?= e($oldValue) ?>" placeholder="<?= e($field['placeholder']) ?>" <?= $required ? 'required' : '' ?> <?= isset($validation['min']) ? 'min="' . e($validation['min']) . '"' : '' ?> <?= isset($validation['max']) ? 'max="' . e($validation['max']) . '"' : '' ?>>
                                        <?php endif; ?>
                                        <?php if ($field['help_text'] && $field['field_type'] !== 'checkbox'): ?><p class="field-help"><?= e($field['help_text']) ?></p><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-8 flex flex-col-reverse justify-between gap-3 sm:flex-row">
                                <?php if ($sectionIndex > 0): ?><button class="button button-secondary" type="button" data-grant-back>← Back</button><?php else: ?><button class="button button-secondary" type="button" data-grant-save>Save for later</button><?php endif; ?>
                                <button class="button button-primary" type="button" data-grant-next>Continue <span aria-hidden="true">→</span></button>
                            </div>
                        </section>
                    <?php endforeach; ?>

                    <section class="grant-step-panel" data-grant-step="<?= $totalSteps ?>">
                        <p class="text-xs font-bold uppercase tracking-[.14em] text-berry">Step <?= $totalSteps ?> of <?= $totalSteps ?></p>
                        <h2 class="mt-3 font-display text-4xl tracking-[-.025em] text-wine sm:text-5xl">Review and consent</h2>
                        <p class="mt-4 leading-7 text-muted">Review every section before submitting. Uploaded files are listed by filename and remain confidential.</p>
                        <div class="mt-8 space-y-5" data-grant-review></div>
                        <div class="mt-7 rounded-[24px] border border-line bg-white p-6 shadow-soft sm:p-8">
                            <h3 class="font-display text-2xl text-wine">Declarations</h3>
                            <div class="mt-5 space-y-5">
                                <label class="consent-row"><input type="checkbox" name="accuracy" value="1" required><span><strong class="block text-wine">Information accuracy</strong>I declare that this application is true, accurate, and complete to the best of my knowledge.</span></label>
                                <label class="consent-row"><input type="checkbox" name="consent" value="1" required><span><strong class="block text-wine">Data-processing consent</strong>I consent to secure storage and processing of my personal and medical information solely for evaluating this grant application.</span></label>
                            </div>
                        </div>
                        <div class="mt-8 flex flex-col-reverse justify-between gap-3 sm:flex-row">
                            <button class="button button-secondary" type="button" data-grant-back>← Back</button>
                            <button class="button button-primary" type="submit">Submit application <span aria-hidden="true">→</span></button>
                        </div>
                    </section>
                </form>
            </div>
        </section>
    <?php endif; ?>
</section>
