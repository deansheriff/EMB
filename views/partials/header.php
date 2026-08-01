<?php
$current = request_path();
$logo = MediaUploader::preferOriginal((string) setting('logo_path', ''));
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
    <div class="mx-auto flex max-w-content items-center gap-3 px-5 py-4 sm:gap-6 lg:px-6">
        <a href="<?= e(url('/')) ?>" class="flex min-w-0 shrink-0 items-center gap-3" aria-label="Emb Chronicles home">
            <?php if ($logo): ?>
                <img src="<?= e(media_url($logo)) ?>" alt="<?= e(setting('site_name')) ?>" class="h-11 w-auto max-w-[150px] object-contain sm:max-w-[180px]">
            <?php else: ?>
                <span class="grid size-11 place-items-center rounded-full bg-wine font-display text-xl text-white">EC</span>
            <?php endif; ?>
            <span class="hidden min-w-0 sm:block">
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
        <div class="ml-auto flex shrink-0 items-center gap-2">
            <button type="button" class="icon-button text-sage" data-chat-open aria-controls="support-chat-panel" aria-expanded="false" aria-label="Open WhatsApp help">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 11.6a8.5 8.5 0 0 1-12.56 7.47L3.5 20.5l1.45-4.27A8.5 8.5 0 1 1 20.5 11.6Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.35 7.9c.18-.42.38-.43.62-.44h.53c.16 0 .33.05.42.3l.72 1.74c.07.2.04.36-.08.53l-.55.7c-.13.16-.1.31-.02.45.5.87 1.2 1.58 2.07 2.08.16.09.3.1.44-.06l.72-.84c.14-.17.32-.2.5-.13l1.83.86c.2.1.28.24.25.46-.1.62-.42 1.2-.9 1.6-.47.39-1.1.57-1.7.43-1.04-.23-2.4-.77-3.85-2.05-1.17-1.04-1.96-2.2-2.3-3.2-.31-.9-.02-1.7.35-2.43Z"/>
                </svg>
            </button>
            <a href="<?= e(url('/appointment')) ?>" class="button button-primary hidden lg:inline-flex">Book a session</a>
            <button type="button" class="icon-button lg:hidden" data-menu-button aria-controls="site-nav" aria-expanded="false" aria-label="Open navigation">☰</button>
        </div>
    </div>
</header>
