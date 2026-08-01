<section class="page-hero">
    <div class="mx-auto grid max-w-content items-end gap-10 px-5 py-16 <?= !empty($event['cover_image']) ? 'lg:grid-cols-[1fr_.9fr]' : '' ?> lg:px-6 lg:py-24">
        <div>
            <nav class="breadcrumb"><a href="<?= e(url('/events')) ?>">Events</a><span>/</span><span><?= e($event['event_type']) ?></span></nav>
            <div class="mt-7 flex flex-wrap gap-2"><span class="chip"><?= e($event['event_type']) ?></span><?php if ($event['is_featured']): ?><span class="chip chip-featured">Featured</span><?php endif; ?></div>
            <h1 class="page-title mt-5"><?= e($event['title']) ?></h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-muted"><?= e($event['excerpt']) ?></p>
        </div>
        <?php if (!empty($event['cover_image'])): ?><img src="<?= e(media_url($event['cover_image'])) ?>" alt="<?= e($event['cover_alt']) ?>" class="aspect-[4/3] w-full rounded-[28px] object-cover shadow-soft"><?php endif; ?>
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto grid max-w-content gap-10 px-5 lg:grid-cols-[300px_1fr] lg:px-6">
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-2xl border border-line bg-ivory p-6">
                <dl class="space-y-5 text-sm">
                    <div><dt class="font-bold text-wine">Date & time</dt><dd class="mt-1 leading-6 text-muted"><?= e(format_date($event['event_date'], 'F j, Y · g:i A')) ?></dd></div>
                    <div><dt class="font-bold text-wine">Location</dt><dd class="mt-1 leading-6 text-muted"><?= e(ucfirst($event['location_mode'])) ?> · <?= e($event['location']) ?></dd></div>
                    <div><dt class="font-bold text-wine">Type</dt><dd class="mt-1 text-muted"><?= e($event['event_type']) ?></dd></div>
                </dl>
                <?php
                $ctaLink = $event['external_link'] ?: ($event['event_type'] === 'Grant Program'
                    ? url('/grant-application/' . $event['slug'])
                    : url('/contact'));
                $ctaLabel = $event['event_type'] === 'Grant Program' ? 'Apply for this grant' : 'Join or register';
                ?>
                <a class="button button-primary mt-7 w-full" href="<?= e($ctaLink) ?>" <?= str_starts_with((string) $ctaLink, 'http') ? 'target="_blank" rel="noopener"' : '' ?>><?= e($ctaLabel) ?></a>
            </div>
        </aside>
        <article>
            <div class="prose-warm prose-large"><?= $event['description'] ?></div>
            <?php if ($event['event_type'] === 'Grant Program'): ?>
                <div class="mt-10 rounded-2xl border border-amber/30 bg-[#FFF7EB] p-6">
                    <h2 class="font-display text-2xl text-wine">A transparent application journey</h2>
                    <ol class="mt-5 grid gap-4 sm:grid-cols-3">
                        <li><span class="step-number">1</span><p class="mt-3 font-bold">Check eligibility</p></li>
                        <li><span class="step-number">2</span><p class="mt-3 font-bold">Submit one complete form</p></li>
                        <li><span class="step-number">3</span><p class="mt-3 font-bold">Wait for a private review</p></li>
                    </ol>
                    <p class="mt-5 text-sm leading-6 text-muted">Eligibility does not guarantee selection. Applications are reviewed with care and privacy.</p>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>

<?php if ($related): ?>
<section class="section">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">Related opportunities</p>
        <h2 class="section-heading mt-4">Continue exploring</h2>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <?php foreach ($related as $item): ?>
                <a class="rounded-2xl border border-line bg-white p-6" href="<?= e(url('/events/' . $item['slug'])) ?>"><span class="chip"><?= e($item['event_type']) ?></span><p class="mt-5 font-display text-2xl text-wine"><?= e($item['title']) ?></p><p class="mt-3 text-sm text-muted"><?= e(format_date($item['event_date'])) ?></p></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
