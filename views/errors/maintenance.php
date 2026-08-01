<?php $supportUrl = trim((string) setting('whatsapp', '')) ?: url('/contact'); ?>
<section class="section min-h-[70vh]">
    <div class="mx-auto max-w-4xl px-5 text-center lg:px-6">
        <div class="rounded-[32px] border border-line bg-white p-8 shadow-soft sm:p-14">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-blush text-sm font-bold uppercase tracking-wider text-wine" aria-hidden="true">Update</span>
            <p class="eyebrow mt-7">Scheduled website update</p>
            <h1 class="page-title mt-4"><?= e($title) ?></h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-muted"><?= e($maintenanceMessage) ?></p>
            <?php if ($maintenanceEndAt): ?><p class="mt-6 font-semibold text-wine">Expected back <?= e(format_date($maintenanceEndAt, 'M j, Y \a\t g:i A')) ?></p><?php endif; ?>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a class="button button-primary" href="<?= e(url(request_path())) ?>">Try again</a>
                <a class="button button-secondary" href="<?= e($supportUrl) ?>" <?= str_starts_with($supportUrl, 'http') ? 'target="_blank" rel="noopener"' : '' ?>>Contact support</a>
            </div>
            <div class="mx-auto mt-10 flex max-w-2xl flex-col gap-3 rounded-2xl bg-ivory p-5 text-left text-sm sm:flex-row sm:items-center">
                <span class="size-2.5 shrink-0 rounded-full bg-amber" aria-hidden="true"></span>
                <p class="text-muted"><strong class="text-wine">Deployment status:</strong> <?= e($statusMessage) ?></p>
                <?php if ($statusUrl): ?><a class="text-link sm:ml-auto" href="<?= e($statusUrl) ?>" target="_blank" rel="noopener">Status updates -&gt;</a><?php endif; ?>
            </div>
        </div>
    </div>
</section>
