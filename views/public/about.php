<section class="page-hero">
    <div class="mx-auto grid max-w-content items-end gap-10 px-5 py-20 lg:grid-cols-[1fr_.82fr] lg:px-6 lg:py-28">
        <div>
            <p class="eyebrow">About Emb Chronicles</p>
            <h1 class="page-title mt-5">Science, empathy, and a clear way forward</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-muted">Fertility information should create clarity—not more fear. We make complex science easier to understand while protecting space for the human experience behind every question.</p>
        </div>
        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1200&q=85" alt="A Black female health professional in a bright clinical environment" class="aspect-[4/3] w-full rounded-[28px] object-cover shadow-soft">
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto grid max-w-content gap-12 px-5 lg:grid-cols-[.75fr_1.25fr] lg:px-6">
        <div>
            <p class="eyebrow">Who we are</p>
            <h2 class="section-heading mt-4">A safe space between the clinic and the outside world</h2>
        </div>
        <div class="prose-warm text-lg"><?= page_content('about', 'intro') ?></div>
    </div>
</section>

<section class="section">
    <div class="mx-auto grid max-w-content items-center gap-12 px-5 lg:grid-cols-2 lg:px-6">
        <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=85" alt="A mentor speaking with people around a table" class="aspect-[4/5] w-full rounded-[28px] object-cover">
        <div class="lg:pl-10">
            <p class="eyebrow">Meet your guide</p>
            <h2 class="section-heading mt-4">Zubaida’s dream-chasing philosophy</h2>
            <div class="prose-warm mt-6">
                <p>With a B.Sc. in Microbiology and advanced post-graduate certification in Assisted Reproductive Technology from IMSA, Zubaida has spent nearly four years working in a leading fertility-clinic environment in Abuja.</p>
                <p>Emb Chronicles grew from a simple conviction: people deserve the context, language, and confidence to participate more fully in decisions about their bodies, treatment, and careers.</p>
            </div>
            <a class="button button-primary mt-8" href="<?= e(url('/appointment')) ?>">Book a conversation</a>
        </div>
    </div>
</section>

<section class="section bg-blush/60">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">What guides us</p>
        <h2 class="section-heading mt-4">Our values in practice</h2>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['01', 'Clarity over complexity', 'Explain the science in language people can use.'],
                ['02', 'Compassion without judgment', 'Make space for questions, uncertainty, and the whole person.'],
                ['03', 'Evidence with context', 'Connect information to the decisions and conversations ahead.'],
                ['04', 'Community that moves with you', 'Build support systems for fertility journeys and STEM careers.'],
            ] as [$number, $heading, $text]): ?>
                <article class="rounded-2xl border border-line bg-white p-6">
                    <span class="text-sm font-bold text-berry"><?= e($number) ?></span>
                    <h3 class="mt-8 font-display text-2xl text-wine"><?= e($heading) ?></h3>
                    <p class="mt-3 text-sm leading-6 text-muted"><?= e($text) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="mx-auto max-w-4xl px-5 text-center">
        <p class="eyebrow">Three connected pathways</p>
        <h2 class="section-heading mt-4">Education. Community. Practical support.</h2>
        <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-muted">Our consultations, communities, and FIYFF Foundation each answer a different part of the same need: informed people should not have to navigate difficult systems alone.</p>
        <div class="mt-9 flex flex-wrap justify-center gap-3">
            <a class="button button-primary" href="<?= e(url('/services')) ?>">Explore services</a>
            <a class="button button-secondary" href="<?= e(url('/community')) ?>">Find your community</a>
        </div>
    </div>
</section>

