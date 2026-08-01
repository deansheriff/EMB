<section class="page-hero">
    <div class="mx-auto grid max-w-content items-end gap-10 px-5 py-16 <?= !empty($service['cover_image']) ? 'lg:grid-cols-[1fr_.9fr]' : '' ?> lg:px-6 lg:py-24">
        <div>
            <nav class="breadcrumb"><a href="<?= e(url('/services')) ?>">Services</a><span>/</span><span><?= e($service['title']) ?></span></nav>
            <span class="chip mt-7">Fertility consultation</span>
            <h1 class="page-title mt-5"><?= e($service['title']) ?></h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-muted"><?= e($service['excerpt']) ?></p>
            <a class="button button-primary mt-8" href="<?= e(url('/appointment?service=' . rawurlencode($service['title']))) ?>">Book this session</a>
        </div>
        <?php if (!empty($service['cover_image'])): ?><img src="<?= e(media_url($service['cover_image'])) ?>" alt="<?= e($service['cover_alt']) ?>" class="aspect-[4/3] w-full rounded-[28px] object-cover shadow-soft"><?php endif; ?>
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto grid max-w-content gap-10 px-5 lg:grid-cols-[260px_1fr] lg:px-6">
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-2xl border border-line bg-ivory p-6">
                <p class="text-xs font-bold uppercase tracking-[.14em] text-muted">Session information</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div><dt class="font-bold text-wine">Format</dt><dd class="mt-1 text-muted">Private consultation</dd></div>
                    <div><dt class="font-bold text-wine">Location</dt><dd class="mt-1 text-muted">Online / as arranged</dd></div>
                    <div><dt class="font-bold text-wine">Best for</dt><dd class="mt-1 text-muted">Questions, reports, clinic preparation</dd></div>
                </dl>
                <a class="button button-primary mt-6 w-full" href="<?= e(url('/appointment')) ?>">Book now</a>
            </div>
            <div class="mt-5 rounded-2xl bg-blush p-6 text-sm leading-6 text-muted">
                <strong class="text-wine">Important:</strong> This service provides education and guidance. It does not replace diagnosis or treatment from a licensed medical team.
            </div>
        </aside>
        <article class="prose-warm prose-large"><?= $service['description'] ?></article>
    </div>
</section>

<?php if ($gallery): ?>
<section class="section">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">A closer look</p>
        <h2 class="section-heading mt-4">Session gallery</h2>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <?php foreach ($gallery as $item): ?><img src="<?= e(media_url($item['image_path'])) ?>" alt="<?= e($item['alt_text']) ?>" class="aspect-[4/3] w-full rounded-2xl object-cover" loading="lazy"><?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="section bg-blush/50">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">Related support</p>
        <h2 class="section-heading mt-4">Continue exploring</h2>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <?php foreach ($related as $item): ?>
                <a class="rounded-2xl border border-line bg-white p-6 transition hover:-translate-y-1 hover:shadow-soft" href="<?= e(url('/services/' . $item['slug'])) ?>">
                    <p class="font-display text-2xl text-wine"><?= e($item['title']) ?></p>
                    <p class="mt-3 text-sm leading-6 text-muted"><?= e($item['excerpt']) ?></p>
                    <span class="text-link mt-5">Learn more →</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
