<section class="page-hero overflow-hidden">
    <div class="mx-auto grid max-w-content items-center gap-12 px-5 py-20 lg:grid-cols-[1fr_.9fr] lg:px-6 lg:py-28">
        <div>
            <p class="eyebrow">Fatima Ibrahim Yakubu Fertility Foundation</p>
            <h1 class="page-title mt-5">Making the path to parenthood more supported</h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-muted">FIYFF brings awareness, advocacy, and practical financial support into one compassionate foundation program.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <?php if ($grant): ?><a class="button button-primary" href="<?= e(url('/events/' . $grant['slug'])) ?>">View current grant</a><?php endif; ?>
                <a class="button button-secondary" href="<?= e(url('/contact')) ?>">Partner with FIYFF</a>
            </div>
        </div>
        <div class="relative">
            <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85" alt="A diverse group sharing a supportive conversation" class="aspect-[4/5] w-full rounded-[28px] object-cover shadow-soft">
            <div class="absolute -bottom-6 left-4 rounded-2xl bg-wine p-6 text-white lg:-left-8"><p class="font-display text-2xl">Awareness · Advocacy · Aid</p></div>
        </div>
    </div>
</section>

<section class="section bg-white">
    <div class="mx-auto grid max-w-content gap-12 px-5 lg:grid-cols-[.7fr_1.3fr] lg:px-6">
        <div><p class="eyebrow">Our mission</p><h2 class="section-heading mt-4">Support that meets people where they are</h2></div>
        <div class="prose-warm text-lg"><?= page_content('fiyff', 'mission') ?></div>
    </div>
</section>

<section class="section bg-blush/55">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <div class="grid gap-5 md:grid-cols-3">
            <?php foreach ([
                ['01', 'Awareness', 'Create plain-language fertility education and open conversations that reduce stigma.'],
                ['02', 'Advocacy', 'Help individuals and couples approach care with stronger questions and more confidence.'],
                ['03', 'Financial grants', 'Offer structured, transparent support opportunities for eligible applicants.'],
            ] as [$number, $heading, $text]): ?>
                <article class="rounded-2xl border border-line bg-white p-7"><span class="text-sm font-bold text-berry"><?= e($number) ?></span><h2 class="mt-8 font-display text-3xl text-wine"><?= e($heading) ?></h2><p class="mt-4 leading-7 text-muted"><?= e($text) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($grant): ?>
<section class="section">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <article class="grid overflow-hidden rounded-[28px] border border-line bg-white shadow-soft lg:grid-cols-2">
            <img src="<?= e(media_url($grant['cover_image'])) ?>" alt="<?= e($grant['cover_alt']) ?>" class="h-full min-h-[360px] w-full object-cover">
            <div class="p-8 lg:p-12">
                <div class="flex flex-wrap gap-2"><span class="chip">Grant program</span><span class="chip chip-featured">Featured</span></div>
                <p class="mt-8 font-display text-5xl text-wine">₦500,000</p>
                <h2 class="mt-3 font-display text-3xl text-wine"><?= e($grant['title']) ?></h2>
                <p class="mt-5 leading-7 text-muted"><?= e($grant['excerpt']) ?></p>
                <a class="button button-primary mt-7" href="<?= e(url('/events/' . $grant['slug'])) ?>">View eligibility</a>
            </div>
        </article>
    </div>
</section>
<?php endif; ?>

<section class="section bg-white">
    <div class="mx-auto max-w-content px-5 lg:px-6">
        <p class="eyebrow">How applications work</p>
        <h2 class="section-heading mt-4">A clear process from interest to decision</h2>
        <ol class="mt-12 grid gap-5 md:grid-cols-4">
            <?php foreach ([
                ['Read', 'Review the event, eligibility, timeline, and privacy information.'],
                ['Prepare', 'Gather the information requested before starting the form.'],
                ['Submit', 'Send one complete application and keep your reference code.'],
                ['Review', 'The FIYFF team assesses applications privately and communicates decisions.'],
            ] as $index => [$heading, $text]): ?>
                <li class="rounded-2xl border border-line p-6"><span class="step-number"><?= $index + 1 ?></span><h3 class="mt-6 font-display text-2xl text-wine"><?= e($heading) ?></h3><p class="mt-3 text-sm leading-6 text-muted"><?= e($text) ?></p></li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<section class="section">
    <div class="mx-auto grid max-w-content gap-12 px-5 lg:grid-cols-2 lg:px-6">
        <div>
            <p class="eyebrow">Common questions</p>
            <h2 class="section-heading mt-4">Before you apply</h2>
        </div>
        <div class="divide-y divide-line border-y border-line" data-accordion>
            <?php foreach ([
                ['Does eligibility guarantee selection?', 'No. Eligibility allows an application to be reviewed; it does not guarantee selection or an award.'],
                ['How is submitted information handled?', 'Application data is restricted to authorised administrators and used for review, communication, and program administration.'],
                ['Can I apply more than once?', 'Submit one complete application for each grant cycle unless the published grant information says otherwise.'],
            ] as [$question, $answer]): ?>
                <div class="py-5"><button class="accordion-trigger" aria-expanded="false"><?= e($question) ?><span>+</span></button><div class="accordion-panel" hidden><p class="pt-4 leading-7 text-muted"><?= e($answer) ?></p></div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

