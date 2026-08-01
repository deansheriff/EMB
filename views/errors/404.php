<?php $adminError = $adminError ?? false; ?>
<section class="section min-h-[65vh]">
    <div class="mx-auto max-w-5xl px-5 lg:px-6">
        <div class="rounded-[28px] border border-line bg-white p-8 shadow-soft sm:p-14">
            <div class="grid items-center gap-10 lg:grid-cols-[.7fr_1.3fr]">
                <div><p class="font-display text-8xl text-blush">404</p><p class="eyebrow mt-4">Page not found</p></div>
                <div>
                    <h1 class="section-heading">We could not find that page.</h1>
                    <p class="mt-5 max-w-2xl leading-7 text-muted">The address may be incorrect, the content may have moved, or the page may no longer be published.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <?php if ($adminError): ?>
                            <a class="button button-primary" href="<?= e(url('/admin')) ?>">Return to dashboard</a>
                        <?php else: ?>
                            <a class="button button-primary" href="<?= e(url('/')) ?>">Return home</a>
                            <a class="button button-secondary" href="<?= e(url('/services')) ?>">Browse services</a>
                            <a class="button button-secondary" href="<?= e(url('/events')) ?>">View events</a>
                            <a class="button button-secondary" href="<?= e(url('/contact')) ?>">Contact us</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!$adminError): ?>
                <div class="mt-10 flex flex-col gap-3 rounded-2xl bg-ivory p-5 text-sm sm:flex-row sm:items-center">
                    <span class="size-2.5 shrink-0 rounded-full bg-sage" aria-hidden="true"></span>
                    <p class="text-muted"><strong class="text-wine">Website status:</strong> <?= e($statusMessage ?? 'All website services are operational.') ?></p>
                    <?php if (!empty($statusUrl)): ?><a class="text-link sm:ml-auto" href="<?= e($statusUrl) ?>" target="_blank" rel="noopener">View status page -&gt;</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
