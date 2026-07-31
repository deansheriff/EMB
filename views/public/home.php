<section class="hero-shell" data-hero>
    <?php if ($heroes): ?>
        <?php foreach ($heroes as $index => $hero): ?>
            <article class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>" data-hero-slide aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                <div class="mx-auto grid min-h-[650px] max-w-content items-center gap-10 px-5 py-16 lg:grid-cols-[.88fr_1.12fr] lg:px-6 lg:py-20">
                    <div class="relative z-10 max-w-2xl">
                        <p class="eyebrow">Fertility education & advocacy</p>
                        <h1 class="mt-5 font-display text-5xl font-semibold leading-[1.02] tracking-[-.035em] text-wine sm:text-6xl lg:text-7xl"><?= e($hero['headline']) ?></h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-muted"><?= e($hero['subheading']) ?></p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <?php if ($hero['cta_label']): ?><a class="button button-primary" href="<?= e(url($hero['cta_link'])) ?>"><?= e($hero['cta_label']) ?></a><?php endif; ?>
                            <?php if ($hero['secondary_label']): ?><a class="button button-secondary" href="<?= e(url($hero['secondary_link'])) ?>"><?= e($hero['secondary_label']) ?></a><?php endif; ?>
                        </div>
                    </div>
                    <div class="hero-media">
                        <img src="<?= e(media_url($hero['image_path'])) ?>" alt="<?= e($hero['image_alt']) ?>" fetchpriority="<?= $index === 0 ? 'high' : 'low' ?>">
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (count($heroes) > 1): ?>
            <div class="hero-controls" role="group" aria-label="Hero slides">
                <button type="button" data-hero-prev aria-label="Previous slide">←</button>
                <span data-hero-status>1 / <?= count($heroes) ?></span>
                <button type="button" data-hero-next aria-label="Next slide">→</button>
                <button type="button" data-hero-pause aria-label="Pause carousel">Pause</button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="mx-auto max-w-content px-5 py-28 text-center lg:px-6">
            <p class="eyebrow">Emb Chronicles</p>
            <h1 class="section-heading mt-4">Fertility clarity, compassion, and community.</h1>
            <a class="button button-primary mt-8" href="<?= e(url('/appointment')) ?>">Make an appointment</a>
        </div>
    <?php endif; ?>
</section>

<?php $welcomeMedia = query_one("SELECT image_path, image_alt FROM page_content WHERE page_key='home' AND section_key='welcome' AND status='published' LIMIT 1"); ?>
<section class="section" data-reveal>
    <div class="mx-auto grid max-w-content items-center gap-12 px-5 lg:grid-cols-2 lg:px-6">
        <?php if (!empty($welcomeMedia['image_path'])): ?><div class="relative">
            <img class="aspect-[4/5] w-full rounded-[28px] object-cover shadow-soft" src="<?= e(media_url($welcomeMedia['image_path'])) ?>" alt="<?= e($welcomeMedia['image_alt']) ?>" loading="lazy">
            <div class="absolute -bottom-6 right-4 max-w-[230px] rounded-2xl bg-wine p-5 text-white shadow-soft lg:-right-7">
                <p class="font-display text-xl">Your fertility bestie</p>
                <p class="mt-2 text-xs leading-5 text-white/70">A clearer space between the clinic and everyday life.</p>
            </div>
        </div><?php endif; ?>
        <div class="<?= !empty($welcomeMedia['image_path']) ? 'lg:pl-12' : 'max-w-3xl lg:col-span-2' ?>">
            <p class="eyebrow">Welcome to your beginning</p>
            <h2 class="section-heading mt-4">Welcome to EMB Chronicles</h2>
            <div class="prose-warm mt-6"><?= page_content('home', 'welcome') ?></div>
            <a class="text-link mt-7" href="<?= e(url('/about')) ?>">More about us <span>→</span></a>
        </div>
    </div>
</section>

<section class="section bg-blush/65" data-reveal>
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="max-w-2xl">
            <p class="eyebrow">Three pillars of our work</p>
            <h2 class="section-heading mt-4">How we support you</h2>
        </div>
        <div class="mt-12 grid gap-5 lg:grid-cols-3">
            <a class="pillar-card" href="<?= e(url('/services')) ?>">
                <span class="pillar-icon">♡</span>
                <p class="font-display text-3xl text-wine">Fertility consultation</p>
                <p>Expert guidance for individuals and couples navigating IVF, infertility diagnosis, results, and treatment decisions.</p>
                <span class="text-link">Explore services →</span>
            </a>
            <a class="pillar-card border-sage/20 bg-[#F0F6F2]" href="<?= e(url('/community')) ?>">
                <span class="pillar-icon bg-sage text-white">⌁</span>
                <p class="font-display text-3xl text-wine">STEM career mentorship</p>
                <p>A thriving community for science graduates seeking roadmaps, mentorship, and entry into Assisted Reproductive Technology.</p>
                <span class="text-link text-sage">Join the community →</span>
            </a>
            <a class="pillar-card border-amber/20 bg-[#FFF7EB]" href="<?= e(url('/fiyff-foundation')) ?>">
                <span class="pillar-icon bg-amber text-white">☀</span>
                <p class="font-display text-3xl text-wine">FIYFF Foundation</p>
                <p>Awareness, advocacy, and practical financial support for eligible couples on the path to parenthood.</p>
                <span class="text-link text-amber">Learn more →</span>
            </a>
        </div>
    </div>
</section>

<?php if ($events): ?>
<section class="section" data-reveal>
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="flex items-end justify-between gap-5">
            <div>
                <p class="eyebrow">What is happening</p>
                <h2 class="section-heading mt-4">Featured events</h2>
            </div>
            <a class="text-link hidden sm:inline-flex" href="<?= e(url('/events')) ?>">View all events →</a>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-2">
            <?php foreach ($events as $event): ?>
                <article class="event-card <?= $event['event_type'] === 'Grant Program' ? 'lg:row-span-2' : '' ?>">
                    <a href="<?= e(url('/events/' . $event['slug'])) ?>" class="block overflow-hidden rounded-t-[20px]">
                        <img class="aspect-[16/10] w-full object-cover transition duration-500 hover:scale-[1.03]" src="<?= e(media_url($event['cover_image'])) ?>" alt="<?= e($event['cover_alt']) ?>" loading="lazy">
                    </a>
                    <div class="p-6 lg:p-7">
                        <div class="flex flex-wrap gap-2">
                            <span class="chip"><?= e($event['event_type']) ?></span>
                            <span class="chip chip-featured">Featured</span>
                        </div>
                        <h3 class="mt-5 font-display text-3xl text-wine"><a href="<?= e(url('/events/' . $event['slug'])) ?>"><?= e($event['title']) ?></a></h3>
                        <p class="mt-3 text-sm font-bold text-sage"><?= e(format_date($event['event_date'], 'M j, Y · g:i A')) ?></p>
                        <p class="mt-4 leading-7 text-muted"><?= e($event['excerpt']) ?></p>
                        <a class="text-link mt-5" href="<?= e(url('/events/' . $event['slug'])) ?>">View details →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <a class="button button-secondary mt-8 sm:hidden" href="<?= e(url('/events')) ?>">View all events</a>
    </div>
</section>
<?php endif; ?>

<section class="section bg-white" data-reveal>
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="max-w-2xl">
            <p class="eyebrow">Fertility & IVF education</p>
            <h2 class="section-heading mt-4">Support for the questions in front of you</h2>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <a href="<?= e(url('/services/' . $service['slug'])) ?>">
                        <img src="<?= e(media_url($service['cover_image'])) ?>" alt="<?= e($service['cover_alt']) ?>" class="aspect-[4/3] w-full object-cover" loading="lazy">
                    </a>
                    <div class="p-6">
                        <h3 class="font-display text-2xl text-wine"><a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                        <p class="mt-3 text-sm leading-6 text-muted"><?= e($service['excerpt']) ?></p>
                        <a class="text-link mt-5" href="<?= e(url('/services/' . $service['slug'])) ?>">More info →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <a class="button button-secondary mt-9" href="<?= e(url('/services')) ?>">More programs</a>
    </div>
</section>

<?php if ($testimonials): ?>
<section class="section bg-[#FAF2F3]" data-testimonials data-reveal>
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="grid gap-12 lg:grid-cols-[.7fr_1.3fr]">
            <div>
                <p class="eyebrow">Testimonials</p>
                <h2 class="section-heading mt-4">Kind words from people we have supported</h2>
                <div class="mt-8 flex gap-2">
                    <button class="carousel-button" data-testimonial-prev aria-label="Previous testimonial">←</button>
                    <button class="carousel-button" data-testimonial-next aria-label="Next testimonial">→</button>
                </div>
            </div>
            <div class="testimonial-stage">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <figure class="testimonial <?= $index === 0 ? 'is-active' : '' ?>" data-testimonial aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
                        <blockquote>“<?= e($testimonial['quote']) ?>”</blockquote>
                        <figcaption class="mt-7 flex items-center gap-4">
                            <?php if ($testimonial['photo_path']): ?><img src="<?= e(media_url($testimonial['photo_path'])) ?>" alt="<?= e($testimonial['photo_alt']) ?>" loading="lazy"><?php endif; ?>
                            <span class="font-bold text-wine"><?= e($testimonial['client_name']) ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="bg-wine py-14 text-white" data-reveal>
    <div class="mx-auto grid max-w-content gap-8 px-5 sm:grid-cols-2 lg:px-6">
        <div class="border-l border-white/20 pl-6">
            <p class="font-display text-5xl" data-count="<?= e(setting('stats_members', 4000)) ?>">0</p>
            <p class="mt-2 text-sm text-white/70">STEM community members</p>
        </div>
        <div class="border-l border-white/20 pl-6">
            <?php if ((int) setting('stats_families', 0) > 0): ?>
                <p class="font-display text-5xl" data-count="<?= e(setting('stats_families')) ?>">0</p>
                <p class="mt-2 text-sm text-white/70">Families given clarity on their journey</p>
            <?php else: ?>
                <p class="font-display text-4xl">Clarity that grows</p>
                <p class="mt-2 text-sm text-white/70">Update this impact number from the admin dashboard when verified.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section" data-reveal>
    <div class="mx-auto max-w-4xl px-5 text-center">
        <p class="eyebrow">Why we exist</p>
        <h2 class="section-heading mt-4">To be your safe space for everything needed on your fertility journey.</h2>
        <a class="button button-primary mt-8" href="<?= e(url('/contact')) ?>">Contact us</a>
    </div>
</section>
