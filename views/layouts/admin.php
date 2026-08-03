<?php
$user = auth_user();
$adminPath = request_path();
$nav = [
    '/admin' => ['Dashboard', 'grid', 'dashboard.view'],
    '/admin/events' => ['Events', 'calendar', 'events.manage'],
    '/admin/services' => ['Services', 'heart', 'services.manage'],
    '/admin/hero-slides' => ['Hero slides', 'image', 'heroes.manage'],
    '/admin/testimonials' => ['Testimonials', 'quote', 'testimonials.manage'],
    '/admin/page-content' => ['Page content', 'file', 'content.manage'],
    '/admin/grant-forms' => ['Grant forms', 'form', 'grants.manage'],
    '/admin/grant-applications' => ['Grant applications', 'grant', 'grants.manage'],
    '/admin/contact-submissions' => ['Contact submissions', 'mail', 'contacts.manage'],
    '/admin/appointments' => ['Appointments', 'clock', 'appointments.manage'],
    '/admin/appointment-types' => ['Appointment types', 'heart', 'appointments.manage'],
    '/admin/availability' => ['Availability', 'calendar', 'appointments.manage'],
    '/admin/email-log' => ['Email log', 'mail', 'email_log.view'],
    '/admin/data-tools' => ['Import / export', 'file', ['services.manage', 'events.manage', 'testimonials.manage', 'contacts.manage', 'grants.manage']],
    '/admin/settings' => ['Site settings', 'settings', 'settings.manage'],
    '/admin/users' => ['Administrators', 'users', 'users.manage'],
    '/admin/roles' => ['Roles & access', 'lock', 'roles.manage'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Admin') ?> | EMB Admin</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="admin-body bg-[#F7F2F0] font-sans text-ink antialiased">
    <a href="#admin-main" class="skip-link">Skip to content</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[264px_1fr]">
        <aside id="admin-sidebar" class="admin-sidebar" aria-label="Admin navigation">
            <div class="mb-8">
                <a href="<?= e(url('/admin')) ?>" class="block font-display text-2xl text-white">EMB Chronicles</a>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[.18em] text-white/60">Admin console</p>
            </div>
            <nav class="space-y-1">
                <?php foreach ($nav as $href => [$label, $icon, $permission]): ?>
                    <?php if (is_array($permission) ? !can_any($permission) : !can($permission)) continue; ?>
                    <?php $active = $href === '/admin' ? in_array($adminPath, ['/admin', '/admin/dashboard'], true) : str_starts_with($adminPath, $href); ?>
                    <a href="<?= e(url($href)) ?>" class="admin-nav-link <?= $active ? 'is-active' : '' ?>">
                        <span aria-hidden="true"><?= e(match ($icon) {
                            'grid' => '⊞', 'calendar' => '▣', 'heart' => '♡', 'image' => '▧',
                            'quote' => '❞', 'file' => '≡', 'form' => '▤', 'grant' => '◇', 'mail' => '✉',
                            'clock' => '◷', 'users' => '◎', 'lock' => '◆', default => '⚙'
                        }) ?></span>
                        <?= e($label) ?>
                        <?php if ($href === '/admin/contact-submissions'): ?>
                            <?php $unread = (int) query_value('SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0 AND is_archived = 0'); ?>
                            <?php if ($unread): ?><span class="ml-auto rounded-full bg-white px-2 py-0.5 text-xs text-wine"><?= $unread ?></span><?php endif; ?>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="min-w-0">
            <header class="admin-topbar">
                <button type="button" class="icon-button lg:hidden" data-admin-menu aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open admin navigation">☰</button>
                <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="text-sm font-semibold text-wine hover:underline">View site ↗</a>
                <div class="ml-auto flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold"><?= e($user['name'] ?? 'Admin') ?></p>
                        <p class="text-xs text-muted"><?= e($user['role'] ?? '') ?></p>
                    </div>
                    <span class="grid size-10 place-items-center rounded-full bg-blush font-bold text-wine" aria-hidden="true"><?= e(mb_substr($user['name'] ?? 'A', 0, 1)) ?></span>
                    <form action="<?= e(url('/admin/logout')) ?>" method="post">
                        <?= csrf_field() ?>
                        <button class="text-sm font-semibold text-muted hover:text-wine">Sign out</button>
                    </form>
                </div>
            </header>
            <?php require BASE_PATH . '/views/partials/flashes.php'; ?>
            <main id="admin-main" class="admin-main"><?= $content ?></main>
        </div>
    </div>
</body>
</html>
