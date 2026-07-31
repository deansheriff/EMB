<header class="admin-page-header">
    <div><p class="text-sm text-muted"><?= e((new DateTimeImmutable())->format('l, F j, Y')) ?></p><h1>Welcome back, <?= e(explode(' ', auth_user()['name'])[0]) ?></h1><p class="mt-2 text-sm text-muted"><?= e(auth_user()['role']) ?></p></div>
    <div class="flex flex-wrap gap-2">
        <?php if (can('events.manage')): ?><a class="button button-secondary" href="<?= e(url('/admin/events')) ?>">+ New event</a><?php endif; ?>
        <?php if (can('services.manage')): ?><a class="button button-secondary" href="<?= e(url('/admin/services')) ?>">+ New service</a><?php endif; ?>
        <?php if (can('grants.manage')): ?><a class="button button-primary" href="<?= e(url('/admin/grant-applications')) ?>">Review grants</a><?php endif; ?>
    </div>
</header>

<?php if ($metrics): ?><section class="admin-metric-grid"><?php foreach ($metrics as [$label, $value, $href, $tone]): ?><a class="metric-card" href="<?= e(url($href)) ?>"><span class="text-sm text-muted"><?= e($label) ?></span><strong class="mt-5 block font-display text-4xl text-wine"><?= (int) $value ?></strong><span class="metric-dot bg-<?= e($tone) ?>"></span></a><?php endforeach; ?></section><?php endif; ?>

<?php if (can('events.manage') || can('contacts.manage')): ?>
<section class="mt-7 grid gap-6 <?= can('events.manage') && can('contacts.manage') ? 'xl:grid-cols-[1.35fr_.65fr]' : '' ?>">
    <?php if (can('events.manage')): ?><div class="admin-card"><div class="admin-card-header"><h2>Upcoming & featured events</h2><a href="<?= e(url('/admin/events')) ?>">View all →</a></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Event</th><th>Date</th><th>Status</th><th>Featured</th></tr></thead><tbody><?php foreach ($upcoming as $event): ?><tr><td><a class="font-semibold text-wine hover:underline" href="<?= e(url('/admin/events?edit=' . $event['id'])) ?>"><?= e($event['title']) ?></a><span class="block text-xs text-muted"><?= e($event['event_type']) ?></span></td><td><?= e(format_date($event['event_date'])) ?></td><td><span class="status status-<?= e($event['status']) ?>"><?= e(ucfirst($event['status'])) ?></span></td><td><?= $event['is_featured'] ? '★ Yes' : '☆ No' ?></td></tr><?php endforeach; ?></tbody></table></div></div><?php endif; ?>
    <?php if (can('contacts.manage')): ?><div class="admin-card"><div class="admin-card-header"><h2>Recent contacts</h2><a href="<?= e(url('/admin/contact-submissions')) ?>">View inbox</a></div><div class="divide-y divide-line"><?php foreach ($contacts as $contact): ?><a class="block py-4" href="<?= e(url('/admin/contact-submissions?view=' . $contact['id'])) ?>"><div class="flex items-center gap-2"><p class="font-semibold"><?= e($contact['name']) ?></p><?php if (!$contact['is_read']): ?><span class="size-2 rounded-full bg-berry"></span><?php endif; ?><time class="ml-auto text-xs text-muted"><?= e(format_date($contact['created_at'], 'M j')) ?></time></div><p class="mt-1 truncate text-sm text-muted"><?= e($contact['message']) ?></p></a><?php endforeach; ?><?php if (!$contacts): ?><div class="py-10 text-center text-sm text-muted">No contact submissions yet.</div><?php endif; ?></div></div><?php endif; ?>
</section>
<?php endif; ?>

<?php if ($heroCount !== null || $showMedia || can_any(['users.manage','roles.manage'])): ?>
<section class="mt-7 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <?php if ($heroCount !== null): ?><div class="admin-card"><div class="admin-card-header"><h2>Hero status</h2><a href="<?= e(url('/admin/hero-slides')) ?>">Manage</a></div><p class="mt-6 font-display text-4xl text-wine"><?= $heroCount ?> active slides</p><p class="mt-3 text-sm text-muted">Homepage rotation is ready.</p></div><?php endif; ?>
    <?php if ($showMedia): ?><div class="admin-card"><div class="admin-card-header"><h2>Media readiness</h2></div><p class="mt-6 text-sm leading-6 text-muted"><?= extension_loaded('gd') ? 'Responsive WebP variants are generated for uploaded images.' : 'Enable the PHP GD extension in production to generate responsive WebP variants.' ?></p></div><?php endif; ?>
    <?php if (can_any(['users.manage','roles.manage'])): ?><div class="admin-card"><div class="admin-card-header"><h2>Recent activity</h2></div><div class="mt-4 space-y-3"><?php foreach ($activity as $item): ?><div class="flex gap-3 text-sm"><span class="mt-1 size-2 rounded-full bg-sage"></span><p><strong><?= e($item['admin_name'] ?: 'Admin') ?></strong> <?= e($item['action']) ?> <?= e(str_replace('_', ' ', (string) $item['entity_type'])) ?><span class="block text-xs text-muted"><?= e(format_date($item['created_at'], 'M j · g:i A')) ?></span></p></div><?php endforeach; ?><?php if (!$activity): ?><p class="text-sm text-muted">Activity will appear here after the first edit.</p><?php endif; ?></div></div><?php endif; ?>
</section>
<?php endif; ?>

<?php if (!$metrics && !$showMedia && $heroCount === null && !can_any(['users.manage','roles.manage'])): ?><div class="admin-card text-center"><h2 class="font-display text-3xl text-wine">Your dashboard is ready</h2><p class="mt-3 text-muted">Your role currently has dashboard access only. Ask a super administrator to add the areas needed for your work.</p></div><?php endif; ?>
