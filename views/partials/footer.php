<footer class="bg-wine text-white">
    <div class="mx-auto grid max-w-content gap-12 px-5 py-16 md:grid-cols-2 lg:grid-cols-[1.25fr_.8fr_.9fr_1.2fr] lg:px-6">
        <div>
            <p class="font-display text-3xl"><?= e(setting('site_name')) ?></p>
            <p class="mt-4 max-w-sm text-sm leading-7 text-white/75"><?= e(setting('footer_blurb')) ?></p>
            <div class="mt-6 flex gap-3">
                <a class="social-link" href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener">Instagram</a>
                <a class="social-link" href="<?= e(setting('tiktok')) ?>" target="_blank" rel="noopener">TikTok</a>
            </div>
        </div>
        <div>
            <p class="footer-title">Explore</p>
            <nav class="mt-4 grid gap-3 text-sm text-white/75">
                <a href="<?= e(url('/about')) ?>">About us</a>
                <a href="<?= e(url('/services')) ?>">Services</a>
                <a href="<?= e(url('/events')) ?>">Events</a>
                <a href="<?= e(url('/community')) ?>">Communities</a>
                <a href="<?= e(url('/privacy')) ?>">Privacy</a>
            </nav>
        </div>
        <div>
            <p class="footer-title">Contact</p>
            <address class="mt-4 space-y-3 text-sm not-italic leading-6 text-white/75">
                <p><?= e(setting('address')) ?></p>
                <p><a href="tel:<?= e(preg_replace('/\s+/', '', (string) setting('phone'))) ?>"><?= e(setting('phone')) ?></a></p>
                <p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p>
                <p><?= e(setting('opening_hours')) ?></p>
            </address>
        </div>
        <div>
            <p class="footer-title">Community updates</p>
            <p class="mt-4 text-sm leading-6 text-white/70">Occasional education, events, and community opportunities—never noise.</p>
            <form action="<?= e(url('/newsletter')) ?>" method="post" class="mt-5 flex gap-2">
                <?= csrf_field() ?>
                <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                <label class="sr-only" for="newsletter-email">Email address</label>
                <input id="newsletter-email" name="email" type="email" required placeholder="Email address" class="min-w-0 flex-1 rounded-xl border-white/20 bg-white/10 text-sm text-white placeholder:text-white/50 focus:border-white focus:ring-white">
                <button class="rounded-xl bg-white px-4 font-bold text-wine" aria-label="Subscribe">→</button>
            </form>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-content flex-col gap-2 px-5 py-5 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between lg:px-6">
            <p>© <?= date('Y') ?> <?= e(setting('site_name')) ?>. All rights reserved.</p>
            <p>Education and guidance—not emergency or diagnostic medical care.</p>
        </div>
    </div>
</footer>
<a href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener" class="whatsapp-fab" aria-label="Chat with Emb Chronicles on WhatsApp">
    <span aria-hidden="true">◉</span><span class="hidden sm:inline">WhatsApp</span>
</a>

