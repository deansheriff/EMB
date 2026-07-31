<?php
$current = request_path();
$logo = (string) setting('logo_path', '');
$navItems = [
    '/' => 'Home',
    '/about' => 'About',
    '/services' => 'Services',
    '/fiyff-foundation' => 'FIYFF Foundation',
    '/community' => 'Communities',
    '/events' => 'Events',
    '/contact' => 'Contact',
];
?>
<div class="hidden border-b border-line bg-white/80 py-2 text-xs text-muted md:block">
    <div class="mx-auto flex max-w-content items-center justify-between px-6">
        <p>Call or WhatsApp <a class="font-bold text-wine hover:underline" href="tel:<?= e(preg_replace('/\s+/', '', (string) setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p>
        <p><?= e(setting('opening_hours')) ?></p>
    </div>
</div>
<header class="site-header" data-header>
    <div class="mx-auto flex max-w-content items-center gap-6 px-5 py-4 lg:px-6">
        <a href="<?= e(url('/')) ?>" class="flex shrink-0 items-center gap-3" aria-label="Emb Chronicles home">
            <?php if ($logo): ?>
                <img src="<?= e(media_url($logo)) ?>" alt="<?= e(setting('site_name')) ?>" class="h-11 w-auto">
            <?php else: ?>
                <span class="grid size-11 place-items-center rounded-full bg-wine font-display text-xl text-white">EC</span>
            <?php endif; ?>
            <span>
                <span class="block font-display text-xl font-semibold leading-none text-wine"><?= e(setting('site_name', 'Emb Chronicles')) ?></span>
                <span class="mt-1 block text-[10px] font-bold uppercase tracking-[.19em] text-muted"><?= e(setting('tagline', 'Fertility Education')) ?></span>
            </span>
        </a>
        <nav id="site-nav" class="site-nav" aria-label="Primary navigation">
            <?php foreach ($navItems as $href => $label): ?>
                <?php $active = $href === '/' ? $current === '/' : str_starts_with($current, $href); ?>
                <a href="<?= e(url($href)) ?>" class="<?= $active ? 'is-active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
            <a href="<?= e(url('/appointment')) ?>" class="button button-primary lg:hidden">Book a session</a>
        </nav>
        <div class="ml-auto flex items-center gap-2">
            <a href="<?= e(setting('whatsapp')) ?>" class="icon-button text-sage" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">◉</a>
            <a href="<?= e(url('/appointment')) ?>" class="button button-primary hidden lg:inline-flex">Book a session</a>
            <button type="button" class="icon-button lg:hidden" data-menu-button aria-controls="site-nav" aria-expanded="false" aria-label="Open navigation">☰</button>
        </div>
    </div>
</header>

