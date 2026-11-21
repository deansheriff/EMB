<header class="admin-page-header"><div><p class="text-sm text-muted">Header logo and link previews</p><h1>Branding</h1></div></header>
<form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/branding')) ?>" class="space-y-6" data-unsaved-form>
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="admin-card-header"><h2>Site logo</h2><span class="text-xs text-muted">Shown in the header and footer</span></div>
        <div class="mt-6 form-grid">
            <div class="form-field"><label>Upload logo</label><input class="file-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp"><p class="field-help">A new upload replaces the current logo. Use a transparent PNG or WebP for a background-free logo.</p></div>
            <div class="form-field"><label>Logo path / URL</label><input class="form-control" name="logo_path" value="<?= e($settings['logo_path'] ?? '') ?>" placeholder="/uploads/… or https://…"></div>
            <?php if (!empty($settings['logo_path'])): ?>
                <div class="form-field md:col-span-2">
                    <label>Current logo</label>
                    <div class="rounded-xl border border-line bg-ivory p-4"><img src="<?= e(media_url(MediaUploader::preferOriginal((string) $settings['logo_path']))) ?>" alt="" class="h-16 max-w-full object-contain"></div>
                    <label class="consent-row mt-3"><input type="checkbox" name="remove_logo" value="1"><span>Remove the logo and use the text fallback</span></label>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-header"><h2>Social sharing image</h2><span class="text-xs text-muted">Default preview for shared links</span></div>
        <div class="mt-6 form-grid">
            <div class="form-field">
                <label>Upload social sharing image</label>
                <input class="file-control" type="file" name="social_share_image_file" accept="image/jpeg,image/png,image/webp">
                <p class="field-help">Use a landscape image around 1200 × 630 pixels. A new upload replaces the current image.</p>
            </div>
            <div class="form-field">
                <label>Or image path / URL</label>
                <input class="form-control" type="text" name="social_share_image" value="<?= e($settings['social_share_image'] ?? '') ?>" placeholder="/uploads/… or https://…">
            </div>
            <div class="form-field md:col-span-2">
                <label>Social image alt text</label>
                <input class="form-control" name="social_share_image_alt" value="<?= e($settings['social_share_image_alt'] ?? '') ?>" maxlength="255" placeholder="Describe the image for accessibility">
                <p class="field-help">Required whenever a social sharing image is set.</p>
            </div>
            <?php if (!empty($settings['social_share_image'])): ?>
                <div class="form-field md:col-span-2">
                    <label>Current social sharing image</label>
                    <img src="<?= e(media_url($settings['social_share_image'])) ?>" alt="<?= e($settings['social_share_image_alt'] ?? '') ?>" class="aspect-[1.91/1] w-full max-w-xl rounded-xl object-cover">
                    <label class="consent-row mt-3"><input type="checkbox" name="remove_social_share_image" value="1"><span>Remove the social sharing image</span></label>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-header"><h2>Website colours</h2><span class="text-xs text-muted">Applied to every public page</span></div>
        <p class="mt-3 text-sm text-muted">Pick a colour or type a hex value such as <code>#6E3345</code>. Keep text colours dark and background colours light so pages stay readable.</p>
        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach (THEME_COLORS as $key => [$label, $help, $default]): ?>
                <div class="form-field" data-color-field>
                    <label for="theme_color_<?= e($key) ?>"><?= e($label) ?></label>
                    <div class="flex items-center gap-3">
                        <input type="color" class="h-12 w-14 shrink-0 cursor-pointer rounded-lg border border-line bg-white p-1" value="<?= e(strtolower($colors[$key])) ?>" aria-label="<?= e($label) ?> colour picker" data-color-picker>
                        <input id="theme_color_<?= e($key) ?>" class="form-control font-mono uppercase" name="theme_color_<?= e($key) ?>" value="<?= e($colors[$key]) ?>" maxlength="7" pattern="#?[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?" data-color-text>
                    </div>
                    <p class="field-help"><?= e($help) ?> Default <?= e($default) ?>.</p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-6 rounded-xl border border-line p-5" data-color-preview>
            <p class="text-xs font-bold uppercase tracking-[.12em] text-muted">Preview</p>
            <div class="mt-3 overflow-hidden rounded-xl border" data-preview="line">
                <div class="p-5" data-preview="ivory">
                    <p class="font-display text-2xl" data-preview="wine">A heading in the primary colour</p>
                    <p class="mt-2 text-sm" data-preview="ink">Body text appears like this, <span data-preview="muted">with supporting text in the muted colour.</span></p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <span class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold text-white" data-preview="berry">Main button</span>
                        <span class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold" data-preview="blush">Soft panel</span>
                        <span class="inline-flex min-h-10 items-center rounded-full px-3 text-xs font-bold text-white" data-preview="sage">Secondary</span>
                        <span class="inline-flex min-h-10 items-center rounded-full px-3 text-xs font-bold text-white" data-preview="amber">Highlight</span>
                    </div>
                </div>
                <div class="p-5 text-sm text-white" data-preview="footer">Footer background uses the primary colour.</div>
            </div>
        </div>
        <label class="consent-row mt-5"><input type="checkbox" name="reset_colors" value="1"><span>Reset all colours to the original EMB Chronicles palette when saving</span></label>
    </section>
    <button class="button button-primary">Save branding</button>
</form>
