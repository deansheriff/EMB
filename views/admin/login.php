<main class="grid min-h-screen lg:grid-cols-[.9fr_1.1fr]">
    <section class="hidden bg-wine p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div><p class="font-display text-3xl">EMB Chronicles</p><p class="mt-2 text-sm uppercase tracking-[.18em] text-white/55">Admin console</p></div>
        <div class="max-w-lg"><p class="font-display text-6xl leading-[1.05]">Manage clarity, care, and community in one place.</p><p class="mt-6 leading-8 text-white/70">Update the public experience, publish opportunities, and keep every enquiry safely organised.</p></div>
        <p class="text-xs text-white/45">Authorised administrators only</p>
    </section>
    <section class="grid place-items-center px-5 py-12">
        <div class="w-full max-w-md rounded-[28px] border border-line bg-white p-7 shadow-soft sm:p-9">
            <a href="<?= e(url('/')) ?>" class="font-display text-2xl text-wine lg:hidden">EMB Chronicles</a>
            <p class="eyebrow mt-8 lg:mt-0">Secure administration</p>
            <h1 class="mt-4 font-display text-4xl text-wine">Welcome back</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Sign in to manage the website and submissions.</p>
            <form action="<?= e(url('/admin/login')) ?>" method="post" class="mt-8 space-y-5">
                <?= csrf_field() ?>
                <div class="form-field"><label for="admin-email">Email address</label><input class="form-control" id="admin-email" name="email" type="email" required autocomplete="username"></div>
                <div class="form-field"><label for="admin-password">Password</label><div class="password-field"><input class="form-control pr-16" id="admin-password" name="password" type="password" required autocomplete="current-password"><button type="button" data-password-toggle aria-label="Show password">Show</button></div></div>
                <label class="flex items-center gap-3 text-sm text-muted"><input type="checkbox" name="remember" value="1" class="rounded border-line text-berry focus:ring-berry">Remember this device</label>
                <button class="button button-primary w-full">Sign in</button>
            </form>
            <p class="mt-7 text-center text-xs leading-5 text-muted">Passwords are verified securely. Sessions use HTTP-only cookies and form requests are protected against cross-site forgery.</p>
        </div>
    </section>
</main>

