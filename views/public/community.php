<section class="page-hero">
    <div class="mx-auto max-w-content px-5 py-20 lg:px-6 lg:py-28">
        <p class="eyebrow">For people and possibilities</p>
        <h1 class="page-title mt-5 max-w-5xl">Find clarity. Build confidence. Move forward together.</h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Two connected communities: one for people navigating fertility, and one for science graduates building meaningful careers in reproductive medicine.</p>
    </div>
</section>

<section class="section pt-10">
    <div class="mx-auto grid max-w-content gap-6 px-5 lg:grid-cols-2 lg:px-6">
        <article class="rounded-[28px] border border-line bg-white p-8 shadow-soft lg:p-10">
            <span class="pillar-icon">♡</span>
            <p class="mt-8 eyebrow">For the TTC community</p>
            <h2 class="mt-4 font-display text-4xl text-wine">The safe space for the questions between appointments</h2>
            <p class="mt-5 leading-8 text-muted">Guided discussions, shared experiences, and plain-language fertility education where science and empathy meet.</p>
            <a class="button button-primary mt-7" href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener">Join the TTC community</a>
        </article>
        <article class="rounded-[28px] bg-sage p-8 text-white shadow-soft lg:p-10">
            <span class="pillar-icon bg-white text-sage">⌁</span>
            <p class="mt-8 text-xs font-bold uppercase tracking-[.16em] text-white/65">For STEM graduates</p>
            <h2 class="mt-4 font-display text-4xl">From graduates to specialists</h2>
            <p class="mt-5 leading-8 text-white/75">Career roadmaps, mentorship, and insider guidance into Assisted Reproductive Technology.</p>
            <a class="button mt-7 bg-white text-sage" href="#stem-path">Explore the STEM path</a>
        </article>
    </div>
</section>

<section class="bg-wine py-12 text-white">
    <div class="mx-auto grid max-w-content gap-8 px-5 sm:grid-cols-3 lg:px-6">
        <div><p class="font-display text-5xl">4,000+</p><p class="mt-2 text-sm text-white/70">Community members</p></div>
        <div><p class="font-display text-5xl">Dozens</p><p class="mt-2 text-sm text-white/70">Graduates mentored into ART</p></div>
        <div><p class="font-display text-5xl">4 years</p><p class="mt-2 text-sm text-white/70">Clinical expertise</p></div>
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto grid max-w-content gap-12 px-5 lg:grid-cols-[1.1fr_.9fr] lg:px-6">
        <div>
            <p class="eyebrow">The vision</p>
            <h2 class="section-heading mt-4">Your degree is not a dead end</h2>
            <div class="prose-warm mt-6 text-lg"><?= page_content('community', 'vision') ?></div>
            <a class="button button-primary mt-8" href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener">Join our WhatsApp community</a>
        </div>
        <div class="rounded-[28px] bg-blush p-8">
            <h3 class="font-display text-3xl text-wine">Who is this for?</h3>
            <ul class="mt-6 space-y-4">
                <?php foreach ([
                    'B.Sc. graduates in Biology, Microbiology, Biochemistry, or related life sciences',
                    'Graduates exploring the clinical or laboratory side of reproductive medicine',
                    'Early-career scientists seeking to specialise in Embryology or Andrology',
                    'Anyone who wants a mentor who has walked a similar path',
                ] as $item): ?><li class="flex gap-3 leading-7 text-muted"><span class="check">✓</span><?= e($item) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section id="stem-path" class="section">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">Our mentorship paths</p>
        <h2 class="section-heading mt-4">Build your path in ART</h2>
        <div class="mt-12 divide-y divide-line border-y border-line">
            <?php foreach ([
                ['Career Clarity Consultations', 'A personalised review of your academic background and a practical roadmap toward ART or aligned STEM fields.'],
                ['The Embryology Blueprint', 'Insider mentorship on daily laboratory life, technical expectations, and the soft skills required in fertility clinics.'],
                ['Specialised Training Guidance', 'Direction toward reputable postgraduate programs and certifications that strengthen your profile.'],
                ['Interview & CV Refinement', 'Present laboratory skills, research experience, and scientific interests with greater clarity.'],
                ['The Pathfinders Network', 'Share resources, discuss opportunities, and learn from people already navigating the journey.'],
            ] as $index => [$heading, $text]): ?>
                <article class="grid gap-4 py-7 md:grid-cols-[70px_.7fr_1fr]"><span class="font-display text-3xl text-berry"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3 class="font-display text-2xl text-wine"><?= e($heading) ?></h3><p class="leading-7 text-muted"><?= e($text) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section bg-sage text-white">
    <div class="mx-auto grid max-w-content items-center gap-10 px-5 lg:grid-cols-[1fr_.8fr] lg:px-6">
        <div><p class="text-xs font-bold uppercase tracking-[.16em] text-white/65">Zubaida’s philosophy</p><h2 class="section-heading mt-4 text-white">Be the living blueprint you wished you had</h2></div>
        <div><p class="leading-8 text-white/75">Passion plus the right information can unlock career paths that once looked invisible. Let us make the roadmap easier to see.</p><a class="button mt-7 bg-white text-sage" href="<?= e(url('/appointment')) ?>">Book a career consultation</a></div>
    </div>
</section>

