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
<section class="support-chat" data-support-chat data-whatsapp-url="<?= e(setting('whatsapp')) ?>">
    <div id="support-chat-panel" class="support-chat-panel" role="dialog" aria-labelledby="support-chat-title" hidden data-chat-panel>
        <header class="support-chat-header">
            <span class="support-chat-avatar" aria-hidden="true">EC</span>
            <div class="min-w-0">
                <h2 id="support-chat-title" class="truncate font-display text-xl font-semibold text-white"><?= e(setting('site_name', 'Emb Chronicles')) ?></h2>
                <p class="mt-1 flex items-center gap-2 text-xs text-white/70">
                    <span class="size-2 shrink-0 rounded-full bg-[#69D391]" aria-hidden="true"></span>
                    WhatsApp support during opening hours
                </p>
            </div>
            <button type="button" class="support-chat-close" data-chat-close aria-label="Close help panel">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </header>

        <div class="support-chat-scroll">
            <div class="support-chat-intro">
                <p class="font-bold text-wine">Hello, how can we help?</p>
                <p class="mt-1 text-sm leading-6 text-muted">Browse a quick answer or send our team a WhatsApp message.</p>
            </div>

            <section aria-labelledby="support-chat-faq-title">
                <h3 id="support-chat-faq-title" class="support-chat-label">Frequently asked questions</h3>
                <div class="mt-3 space-y-2">
                    <article class="support-chat-faq">
                        <button type="button" class="support-chat-faq-button" aria-expanded="false" aria-controls="support-faq-services" data-chat-faq>
                            <span>What support does Emb Chronicles offer?</span>
                            <svg class="support-chat-faq-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/></svg>
                        </button>
                        <div id="support-faq-services" class="support-chat-faq-answer" hidden>
                            <p>We provide fertility education, one-to-one consultations, community support, events, and information about FIYFF fertility-support programmes.</p>
                            <a href="<?= e(url('/services')) ?>">Explore our services <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="support-chat-faq">
                        <button type="button" class="support-chat-faq-button" aria-expanded="false" aria-controls="support-faq-booking" data-chat-faq>
                            <span>How do I book a consultation?</span>
                            <svg class="support-chat-faq-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/></svg>
                        </button>
                        <div id="support-faq-booking" class="support-chat-faq-answer" hidden>
                            <p>Choose a session on our appointment page, share your preferred date and contact method, then follow the confirmation steps.</p>
                            <a href="<?= e(url('/appointment')) ?>">Book a session <span aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="support-chat-faq">
                        <button type="button" class="support-chat-faq-button" aria-expanded="false" aria-controls="support-faq-programmes" data-chat-faq>
                            <span>Where can I find events and fertility grants?</span>
                            <svg class="support-chat-faq-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/></svg>
                        </button>
                        <div id="support-faq-programmes" class="support-chat-faq-answer" hidden>
                            <p>Current events, application windows, and eligibility details are published on our Events and FIYFF Foundation pages.</p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1"><a href="<?= e(url('/events')) ?>">View events</a><a href="<?= e(url('/fiyff-foundation')) ?>">About FIYFF</a></div>
                        </div>
                    </article>

                    <article class="support-chat-faq">
                        <button type="button" class="support-chat-faq-button" aria-expanded="false" aria-controls="support-faq-medical" data-chat-faq>
                            <span>Can you diagnose a condition or provide emergency care?</span>
                            <svg class="support-chat-faq-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/></svg>
                        </button>
                        <div id="support-faq-medical" class="support-chat-faq-answer" hidden>
                            <p>No. Our website provides education and guidance, not diagnosis or emergency care. For urgent symptoms, contact a qualified clinician or your local emergency service immediately.</p>
                        </div>
                    </article>

                    <article class="support-chat-faq">
                        <button type="button" class="support-chat-faq-button" aria-expanded="false" aria-controls="support-faq-hours" data-chat-faq>
                            <span>When can I contact the team?</span>
                            <svg class="support-chat-faq-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4"/></svg>
                        </button>
                        <div id="support-faq-hours" class="support-chat-faq-answer" hidden>
                            <p><?= e(setting('opening_hours')) ?>. Messages received outside these hours can be answered when the team returns.</p>
                            <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>
                        </div>
                    </article>
                </div>
            </section>

            <section class="mt-6" aria-labelledby="support-chat-links-title">
                <h3 id="support-chat-links-title" class="support-chat-label">Useful information</h3>
                <nav class="support-chat-links" aria-label="Helpful links">
                    <a href="<?= e(url('/appointment')) ?>"><span>Book an appointment</span><span aria-hidden="true">→</span></a>
                    <a href="<?= e(url('/events')) ?>"><span>Events &amp; programmes</span><span aria-hidden="true">→</span></a>
                    <a href="<?= e(url('/community')) ?>"><span>Join our community</span><span aria-hidden="true">→</span></a>
                    <a href="<?= e(url('/contact')) ?>"><span>Other contact options</span><span aria-hidden="true">→</span></a>
                </nav>
                <p class="mt-4 rounded-xl bg-blush/60 px-4 py-3 text-xs leading-5 text-muted">Please avoid sending sensitive medical records over WhatsApp. Use the protected application or contact forms where provided.</p>
            </section>
        </div>

        <form class="support-chat-composer" action="<?= e(setting('whatsapp')) ?>" method="get" target="_blank" data-chat-form>
            <label for="support-chat-message">Send us a message</label>
            <textarea id="support-chat-message" name="text" rows="3" maxlength="600" placeholder="Hello Emb Chronicles, I’d like to ask about…" data-chat-message></textarea>
            <button type="submit">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 11.6a8.5 8.5 0 0 1-12.56 7.47L3.5 20.5l1.45-4.27A8.5 8.5 0 1 1 20.5 11.6Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.35 7.9c.18-.42.38-.43.62-.44h.53c.16 0 .33.05.42.3l.72 1.74c.07.2.04.36-.08.53l-.55.7c-.13.16-.1.31-.02.45.5.87 1.2 1.58 2.07 2.08.16.09.3.1.44-.06l.72-.84c.14-.17.32-.2.5-.13l1.83.86c.2.1.28.24.25.46-.1.62-.42 1.2-.9 1.6-.47.39-1.1.57-1.7.43-1.04-.23-2.4-.77-3.85-2.05-1.17-1.04-1.96-2.2-2.3-3.2-.31-.9-.02-1.7.35-2.43Z"/>
                </svg>
                Continue on WhatsApp
            </button>
        </form>
    </div>

    <button type="button" class="support-chat-launcher" data-chat-toggle aria-expanded="false" aria-controls="support-chat-panel" aria-label="Open WhatsApp help">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 11.6a8.5 8.5 0 0 1-12.56 7.47L3.5 20.5l1.45-4.27A8.5 8.5 0 1 1 20.5 11.6Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.35 7.9c.18-.42.38-.43.62-.44h.53c.16 0 .33.05.42.3l.72 1.74c.07.2.04.36-.08.53l-.55.7c-.13.16-.1.31-.02.45.5.87 1.2 1.58 2.07 2.08.16.09.3.1.44-.06l.72-.84c.14-.17.32-.2.5-.13l1.83.86c.2.1.28.24.25.46-.1.62-.42 1.2-.9 1.6-.47.39-1.1.57-1.7.43-1.04-.23-2.4-.77-3.85-2.05-1.17-1.04-1.96-2.2-2.3-3.2-.31-.9-.02-1.7.35-2.43Z"/>
        </svg>
        <span class="hidden sm:inline" data-chat-launcher-label>Chat with us</span>
    </button>
</section>
