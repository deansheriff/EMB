<header class="admin-page-header"><div><p class="text-sm text-muted">Brand and global content</p><h1>Site settings</h1></div></header>
<form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/settings')) ?>" class="space-y-6" data-unsaved-form>
    <?= csrf_field() ?>
    <div class="grid gap-6 xl:grid-cols-[1fr_.75fr]">
        <div class="space-y-6">
            <section class="admin-card">
                <div class="admin-card-header"><h2>Branding</h2><span class="text-xs text-muted">Header, footer, and social previews</span></div>
                <div class="mt-6 form-grid">
                    <div class="form-field"><label>Site name</label><input class="form-control" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>"></div>
                    <div class="form-field"><label>Tagline</label><input class="form-control" name="tagline" value="<?= e($settings['tagline'] ?? '') ?>"></div>
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
            <section class="admin-card"><div class="admin-card-header"><h2>Contact details</h2></div><div class="mt-6 form-grid"><div class="form-field"><label>Phone</label><input class="form-control" name="phone" value="<?= e($settings['phone'] ?? '') ?>"></div><div class="form-field"><label>WhatsApp link</label><input class="form-control" type="url" name="whatsapp" value="<?= e($settings['whatsapp'] ?? '') ?>"></div><div class="form-field"><label>Email</label><input class="form-control" type="email" name="email" value="<?= e($settings['email'] ?? '') ?>"></div><div class="form-field"><label>Opening hours</label><input class="form-control" name="opening_hours" value="<?= e($settings['opening_hours'] ?? '') ?>"></div><div class="form-field md:col-span-2"><label>Address</label><textarea class="form-control" name="address"><?= e($settings['address'] ?? '') ?></textarea></div></div></section>
            <section class="admin-card"><div class="admin-card-header"><h2>SMTP email delivery</h2><span class="text-xs text-muted"><?= !empty($settings['smtp_password']) ? 'Password configured' : 'Password required' ?></span></div><div class="mt-6 space-y-5"><div class="form-grid"><label class="consent-row"><input type="checkbox" name="smtp_enabled" value="1" <?= ($settings['smtp_enabled'] ?? '0') === '1' ? 'checked' : '' ?>><span>Enable SMTP email delivery</span></label><label class="consent-row"><input type="checkbox" name="email_confirmations_enabled" value="1" <?= ($settings['email_confirmations_enabled'] ?? '1') === '1' ? 'checked' : '' ?>><span>Send user confirmations and admin notifications</span></label><div class="form-field"><label>SMTP host</label><input class="form-control" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="smtp.example.com"></div><div class="form-field"><label>SMTP port</label><input class="form-control" type="number" min="1" max="65535" name="smtp_port" value="<?= e($settings['smtp_port'] ?? 587) ?>"></div><div class="form-field"><label>Encryption</label><select class="form-control" name="smtp_encryption"><option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (usually port 587)</option><option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SMTPS (usually port 465)</option><option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option></select></div><div class="form-field"><label>SMTP username</label><input class="form-control" name="smtp_username" value="<?= e($settings['smtp_username'] ?? '') ?>" autocomplete="username"></div><div class="form-field"><label>SMTP password</label><input class="form-control" type="password" name="smtp_password" value="" placeholder="<?= !empty($settings['smtp_password']) ? 'Leave blank to keep the saved password' : 'Enter SMTP password' ?>" autocomplete="new-password"></div><label class="consent-row self-end"><input type="checkbox" name="clear_smtp_password" value="1"><span>Clear saved SMTP password</span></label><div class="form-field"><label>From email</label><input class="form-control" type="email" name="smtp_from_email" value="<?= e($settings['smtp_from_email'] ?? '') ?>"></div><div class="form-field"><label>From name</label><input class="form-control" name="smtp_from_name" value="<?= e($settings['smtp_from_name'] ?? '') ?>"></div><div class="form-field"><label>Reply-to email</label><input class="form-control" type="email" name="smtp_reply_to" value="<?= e($settings['smtp_reply_to'] ?? '') ?>"></div><div class="form-field"><label>Admin notification email</label><input class="form-control" type="email" name="smtp_admin_email" value="<?= e($settings['smtp_admin_email'] ?? '') ?>"></div></div><div class="flex flex-wrap items-center gap-3 border-t border-line pt-5"><button class="button button-secondary" name="action" value="test_email">Save settings & send test email</button><p class="text-xs text-muted">The test is sent to the admin notification email above.</p></div></div></section>
            <section class="admin-card"><div class="admin-card-header"><h2>Paystack appointment payments</h2><span class="text-xs text-muted"><?= !empty($settings['paystack_secret_key']) ? 'Secret key configured' : 'Keys required' ?></span></div><div class="mt-6 space-y-5"><label class="consent-row"><input type="checkbox" name="paystack_enabled" value="1" <?= ($settings['paystack_enabled'] ?? '0') === '1' ? 'checked' : '' ?>><span>Require Paystack payment before an appointment enters the scheduling queue</span></label><div class="form-grid"><div class="form-field"><label>Paystack public key</label><input class="form-control" name="paystack_public_key" value="<?= e($settings['paystack_public_key'] ?? '') ?>" placeholder="pk_test_…"></div><div class="form-field"><label>Paystack secret key</label><input class="form-control" type="password" name="paystack_secret_key" value="" placeholder="<?= !empty($settings['paystack_secret_key']) ? 'Leave blank to keep the saved secret key' : 'sk_test_…' ?>" autocomplete="new-password"></div><label class="consent-row"><input type="checkbox" name="clear_paystack_secret_key" value="1"><span>Clear saved Paystack secret key</span></label><div class="form-field"><label>Currency</label><select class="form-control" name="paystack_currency"><option value="NGN">NGN — Nigerian naira</option></select></div><div class="form-field"><label>Appointment fee (NGN)</label><input class="form-control" inputmode="decimal" name="appointment_fee" value="<?= e($settings['appointment_fee'] ?? '0.00') ?>" placeholder="25000.00"><p class="field-help">Enter naira, not kobo. The server converts this amount to the lower denomination for Paystack.</p></div><div class="form-field md:col-span-2"><label>Webhook URL</label><input class="form-control" value="<?= e(url('/payments/paystack/webhook')) ?>" readonly><p class="field-help">Add this exact URL to your Paystack dashboard. Successful events are accepted only when the Paystack signature matches the saved secret key.</p></div></div><div class="rounded-xl border border-amber/25 bg-[#FFF7EB] p-4 text-xs leading-5 text-muted">Start with Paystack test keys. Switch to live keys only after test checkout, callback, webhook, amount verification, and confirmation email have all succeeded on the production HTTPS domain.</div></div></section>
            <section class="admin-card"><div class="admin-card-header"><h2>Social and footer</h2></div><div class="mt-6 form-grid"><div class="form-field"><label>Instagram</label><input class="form-control" type="url" name="instagram" value="<?= e($settings['instagram'] ?? '') ?>"></div><div class="form-field"><label>TikTok</label><input class="form-control" type="url" name="tiktok" value="<?= e($settings['tiktok'] ?? '') ?>"></div><div class="form-field md:col-span-2"><label>Footer blurb</label><textarea class="form-control" name="footer_blurb"><?= e($settings['footer_blurb'] ?? '') ?></textarea></div></div></section>
            <section class="admin-card"><div class="admin-card-header"><h2>Impact statistics</h2><span class="text-xs text-muted">Publish only verified numbers</span></div><div class="mt-6 form-grid"><div class="form-field"><label>STEM community members</label><input class="form-control" type="number" name="stats_members" value="<?= e($settings['stats_members'] ?? 0) ?>"></div><div class="form-field"><label>Families given clarity</label><input class="form-control" type="number" name="stats_families" value="<?= e($settings['stats_families'] ?? 0) ?>"></div></div></section>
            <section class="admin-card">
                <div class="admin-card-header"><h2>Search and social sharing</h2><span class="text-xs text-muted">Default preview for shared links</span></div>
                <div class="mt-6 space-y-5">
                    <div class="form-field">
                        <label>Default meta title</label>
                        <input class="form-control" name="default_meta_title" value="<?= e($settings['default_meta_title'] ?? '') ?>" maxlength="70">
                        <p class="field-help">Used by the homepage and as the fallback title for search and social previews.</p>
                    </div>
                    <div class="form-field">
                        <label>Meta description</label>
                        <textarea class="form-control" name="default_meta_description" maxlength="200" rows="4"><?= e($settings['default_meta_description'] ?? '') ?></textarea>
                        <p class="field-help">A concise summary shown by search engines and social platforms when they generate a link preview.</p>
                    </div>
                    <div class="form-grid">
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
                    </div>
                    <?php if (!empty($settings['social_share_image'])): ?>
                        <div class="form-field">
                            <label>Current social sharing image</label>
                            <img src="<?= e(media_url($settings['social_share_image'])) ?>" alt="<?= e($settings['social_share_image_alt'] ?? '') ?>" class="aspect-[1.91/1] w-full rounded-xl object-cover">
                            <label class="consent-row mt-3"><input type="checkbox" name="remove_social_share_image" value="1"><span>Remove the social sharing image</span></label>
                        </div>
                    <?php endif; ?>
                    <div class="rounded-xl border border-line bg-ivory p-5">
                        <p class="text-xs font-bold uppercase tracking-[.12em] text-muted">Preview</p>
                        <?php if (!empty($settings['social_share_image'])): ?><img src="<?= e(media_url($settings['social_share_image'])) ?>" alt="" class="mt-3 aspect-[1.91/1] w-full rounded-lg object-cover"><?php endif; ?>
                        <p class="mt-4 font-display text-xl text-wine"><?= e($settings['default_meta_title'] ?? 'Emb Chronicles') ?></p>
                        <p class="mt-2 text-sm leading-6 text-muted"><?= e($settings['default_meta_description'] ?? '') ?></p>
                        <p class="mt-3 truncate text-xs uppercase tracking-[.08em] text-muted"><?= e((string) env('APP_URL', '')) ?></p>
                    </div>
                </div>
            </section>
            <section class="admin-card">
                <div class="admin-card-header"><h2>Maintenance and deployment status</h2><span class="text-xs text-muted">Public operational messaging</span></div>
                <div class="mt-6 space-y-5">
                    <label class="consent-row"><input type="checkbox" name="maintenance_enabled" value="1" <?= ($settings['maintenance_enabled'] ?? '0') === '1' ? 'checked' : '' ?>><span>Enable maintenance mode for public pages</span></label>
                    <div class="rounded-xl border border-amber/25 bg-[#FFF7EB] p-4 text-xs leading-5 text-muted">Administrators, the health check, and Paystack callbacks remain available. Public pages return HTTP 503 while maintenance mode is active.</div>
                    <div class="form-field"><label>Maintenance heading</label><input class="form-control" name="maintenance_title" value="<?= e($settings['maintenance_title'] ?? 'We are making the website better') ?>"></div>
                    <div class="form-field"><label>Maintenance message</label><textarea class="form-control" name="maintenance_message" rows="4"><?= e($settings['maintenance_message'] ?? '') ?></textarea></div>
                    <div class="form-field"><label>Expected return time <span class="text-muted">(optional)</span></label><input class="form-control" type="datetime-local" name="maintenance_end_at" value="<?= e(!empty($settings['maintenance_end_at']) ? date('Y-m-d\TH:i', strtotime($settings['maintenance_end_at'])) : '') ?>"></div>
                    <div class="form-field"><label>Deployment status message</label><input class="form-control" name="deployment_status_message" value="<?= e($settings['deployment_status_message'] ?? 'All website services are operational.') ?>"><p class="field-help">Shown on maintenance and page-not-found screens.</p></div>
                    <div class="form-field"><label>External status page URL <span class="text-muted">(optional)</span></label><input class="form-control" type="url" name="deployment_status_url" value="<?= e($settings['deployment_status_url'] ?? '') ?>" placeholder="https://status.example.com"></div>
                </div>
            </section>
        </div>
        <aside class="admin-card self-start xl:sticky xl:top-24"><div class="admin-card-header"><h2>Live brand preview</h2></div><div class="mt-6 overflow-hidden rounded-2xl border border-line"><div class="flex items-center gap-3 bg-white p-5"><span class="grid size-10 place-items-center rounded-full bg-wine text-sm font-bold text-white">EC</span><div><p class="font-display text-lg text-wine"><?= e($settings['site_name'] ?? '') ?></p><p class="text-[9px] font-bold uppercase tracking-[.16em] text-muted"><?= e($settings['tagline'] ?? '') ?></p></div></div><div class="bg-wine p-6 text-white"><p class="font-display text-2xl"><?= e($settings['site_name'] ?? '') ?></p><p class="mt-3 text-xs leading-5 text-white/65"><?= e($settings['footer_blurb'] ?? '') ?></p></div></div><button class="button button-primary mt-6 w-full">Save all settings</button><p class="mt-3 text-center text-xs text-muted">Unsaved changes are not shown on the public site.</p></aside>
    </div>
</form>
