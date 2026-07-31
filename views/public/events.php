<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">Gather, learn, apply</p>
        <h1 class="page-title mt-5">Events, conversations, and opportunities</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Find live education, supportive community conversations, practical STEM sessions, and FIYFF grant programs.</p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <form method="get" action="<?= e(url('/events')) ?>" class="rounded-2xl border border-line bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <label class="sr-only" for="event-search">Search events</label>
                <input id="event-search" name="q" value="<?= e($search) ?>" placeholder="Search events" class="form-control lg:max-w-sm">
                <div class="flex gap-2 overflow-x-auto pb-1 lg:ml-auto">
                    <a class="filter-chip <?= $activeType === '' ? 'is-active' : '' ?>" href="<?= e(url('/events')) ?>">All</a>
                    <?php foreach ($types as $type): ?><a class="filter-chip <?= $activeType === $type['event_type'] ? 'is-active' : '' ?>" href="<?= e(url('/events?type=' . rawurlencode($type['event_type']))) ?>"><?= e($type['event_type']) ?></a><?php endforeach; ?>
                </div>
                <button class="button button-primary">Search</button>
            </div>
        </form>

        <?php if ($events): ?>
            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($events as $event): ?>
                    <article class="event-card <?= $event['is_featured'] ? 'md:col-span-2 lg:col-span-1' : '' ?>">
                        <a href="<?= e(url('/events/' . $event['slug'])) ?>"><img class="aspect-[4/3] w-full rounded-t-[20px] object-cover" src="<?= e(media_url($event['cover_image'])) ?>" alt="<?= e($event['cover_alt']) ?>" loading="lazy"></a>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-2"><span class="chip"><?= e($event['event_type']) ?></span><?php if ($event['is_featured']): ?><span class="chip chip-featured">Featured</span><?php endif; ?></div>
                            <h2 class="mt-5 font-display text-2xl text-wine"><a href="<?= e(url('/events/' . $event['slug'])) ?>"><?= e($event['title']) ?></a></h2>
                            <p class="mt-3 text-sm font-bold text-sage"><?= e(format_date($event['event_date'], 'M j, Y · g:i A')) ?></p>
                            <p class="mt-2 text-sm text-muted"><?= e($event['location']) ?></p>
                            <p class="mt-4 text-sm leading-6 text-muted"><?= e($event['excerpt']) ?></p>
                            <a class="text-link mt-5" href="<?= e(url('/events/' . $event['slug'])) ?>">View details →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state mt-12">
                <div class="text-4xl" aria-hidden="true">○</div>
                <h2 class="mt-4 font-display text-3xl text-wine">No matching events</h2>
                <p class="mt-3 text-muted">Try a different search or clear the active filter.</p>
                <a class="button button-secondary mt-6" href="<?= e(url('/events')) ?>">Clear filters</a>
            </div>
        <?php endif; ?>
    </div>
</section>

