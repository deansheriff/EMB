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
    <button class="button button-primary">Save branding</button>
</form>
