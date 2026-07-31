<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">What we offer</p>
        <h1 class="page-title mt-5 max-w-4xl">How we support your journey</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Expert fertility guidance, clinical translation, and empathetic advocacy—tailored to where you are on your journey.</p>
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="grid gap-8 lg:grid-cols-[.58fr_1.42fr]">
            <div>
                <p class="eyebrow">For individuals & couples</p>
                <h2 class="section-heading mt-4">Fertility consultation services</h2>
                <a class="button button-primary mt-8" href="<?= e(url('/appointment')) ?>">Book a personal session</a>
            </div>
            <ol class="divide-y divide-line border-y border-line">
                <?php foreach ([
                    ['Strategic Clinic Guidance', 'Find the right place to start and learn what matters when evaluating a clinic.'],
                    ['Clinic Journey Advocacy', 'Identify gaps and revisit explanations that felt rushed or unclear.'],
                    ['Laboratory Logic & Translation', 'Turn complex reports and laboratory language into understandable context.'],
                    ['Empathetic Navigation', 'Prepare stronger questions for more productive clinical conversations.'],
                    ['The “Safe Space” Consultation', 'Discuss fears, options, and results in a judgment-free educational space.'],
                ] as $index => [$heading, $text]): ?>
                    <li class="grid gap-3 py-7 sm:grid-cols-[60px_1fr]">
                        <span class="font-display text-3xl text-berry"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <div><h3 class="font-display text-2xl text-wine"><?= e($heading) ?></h3><p class="mt-2 leading-7 text-muted"><?= e($text) ?></p></div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</section>

<section class="section">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">Our services</p>
        <h2 class="section-heading mt-4">Choose the kind of clarity you need</h2>
        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($services as $service): ?>
                <article class="service-card">
                    <a href="<?= e(url('/services/' . $service['slug'])) ?>"><img class="aspect-[4/3] w-full object-cover" src="<?= e(media_url($service['cover_image'])) ?>" alt="<?= e($service['cover_alt']) ?>" loading="lazy"></a>
                    <div class="p-6">
                        <h3 class="font-display text-2xl text-wine"><a href="<?= e(url('/services/' . $service['slug'])) ?>"><?= e($service['title']) ?></a></h3>
                        <p class="mt-3 text-sm leading-6 text-muted"><?= e($service['excerpt']) ?></p>
                        <a class="text-link mt-5" href="<?= e(url('/services/' . $service['slug'])) ?>">More info →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section bg-sage text-white">
    <div class="mx-auto grid max-w-content gap-12 px-5 lg:grid-cols-[.8fr_1.2fr] lg:px-6">
        <div>
            <p class="eyebrow !text-white/70">For science graduates</p>
            <h2 class="section-heading mt-4 text-white">STEM career mentorship & community</h2>
            <p class="mt-6 leading-8 text-white/75">Your degree is not a dead end; it is a foundation. We bridge academic theory and clinical practice in Assisted Reproductive Technology.</p>
            <a class="button mt-8 bg-white text-sage hover:bg-ivory" href="<?= e(url('/community')) ?>">Join the STEM community</a>
        </div>
        <div class="space-y-4">
            <?php foreach ([
                'Career Clarity Consultations (1-on-1)',
                'The Embryology Blueprint (Mentorship)',
                'Specialised Training Guidance',
                'Interview & CV Refinement for Scientists',
                'The “Pathfinders” Networking Group',
            ] as $index => $program): ?>
                <div class="flex items-center gap-5 rounded-2xl border border-white/20 bg-white/10 p-5">
                    <span class="text-sm font-bold text-white/60"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <p class="font-display text-xl"><?= e($program) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

