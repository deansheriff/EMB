<?php
$section = $editing ?: [
    'id' => '',
    'page_key' => 'home',
    'section_key' => '',
    'eyebrow' => '',
    'heading' => '',
    'content' => '',
    'image_path' => '',
    'image_alt' => '',
    'link_label' => '',
    'link_url' => '',
    'status' => 'draft',
];
?>
<header class="admin-page-header">
    <div>
        <p class="text-sm text-muted">Editorial content and page imagery</p>
        <h1>Page content</h1>
        <p class="mt-2 max-w-2xl text-sm text-muted">Manage the homepage welcome image, About page hero and guide images, and FIYFF hero image here.</p>
    </div>
    <a class="button button-primary" href="<?= e(url('/admin/page-content?new=1')) ?>">+ Add section</a>
</header>
<div class="admin-split">
    <section class="admin-card">
        <div class="space-y-2">
            <?php foreach ($sections as $item): ?>
                <a class="block rounded-xl border p-4 <?= (int) ($section['id'] ?: 0) === (int) $item['id'] ? 'border-berry bg-blush/40' : 'border-line bg-white' ?>" href="<?= e(url('/admin/page-content?edit=' . $item['id'])) ?>">
                    <div class="flex items-center gap-3">
                        <p class="font-semibold capitalize text-wine"><?= e(str_replace('-', ' ', $item['page_key'])) ?></p>
                        <span class="status status-<?= e($item['status']) ?> ml-auto"><?= e($item['status']) ?></span>
                    </div>
                    <p class="mt-1 text-xs text-muted"><?= e($item['section_key']) ?> · <?= e($item['heading']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="admin-card">
        <div class="admin-card-header">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.12em] text-muted"><?= $section['id'] ? 'Edit managed section' : 'Create managed section' ?></p>
                <h2 class="mt-1"><?= e($section['heading'] ?: 'New content section') ?></h2>
            </div>
        </div>
        <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/page-content')) ?>" class="mt-6 space-y-6" data-unsaved-form>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($section['id']) ?>">
            <div class="form-grid">
                <div class="form-field"><label>Page key <span>*</span></label><input class="form-control" name="page_key" value="<?= e($section['page_key']) ?>" required></div>
                <div class="form-field"><label>Section key <span>*</span></label><input class="form-control" name="section_key" value="<?= e($section['section_key']) ?>" required></div>
                <div class="form-field"><label>Eyebrow label</label><input class="form-control" name="eyebrow" value="<?= e($section['eyebrow']) ?>"></div>
                <div class="form-field"><label>Heading</label><input class="form-control" name="heading" value="<?= e($section['heading']) ?>"></div>
            </div>
            <div class="form-field">
                <label>Content <span>*</span></label>
                <div class="rich-toolbar" data-rich-toolbar><button type="button" data-tag="h2">Heading</button><button type="button" data-tag="strong">Bold</button><button type="button" data-tag="ul">List</button><button type="button" data-tag="p">Paragraph</button></div>
                <textarea class="form-control min-h-64 font-mono text-sm" name="content" data-rich-text required><?= e($section['content']) ?></textarea>
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Upload supporting image <span class="text-muted">(optional)</span></label>
                    <input class="file-control" type="file" name="supporting_image" accept="image/jpeg,image/png,image/webp">
                    <p class="field-help">Upload a JPG, PNG, or WebP. A new upload replaces the current image.</p>
                </div>
                <div class="form-field">
                    <label>Or image path / URL <span class="text-muted">(optional)</span></label>
                    <input class="form-control" type="text" name="image_path" value="<?= e($section['image_path']) ?>" placeholder="/uploads/… or https://…">
                    <p class="field-help">Use a local upload path or a full external image URL.</p>
                </div>
                <div class="form-field">
                    <label>Image alt text</label>
                    <input class="form-control" name="image_alt" value="<?= e($section['image_alt']) ?>">
                    <p class="field-help">Required only when an image is supplied.</p>
                </div>
                <?php if (!empty($section['image_path'])): ?>
                    <div class="form-field">
                        <label>Current image</label>
                        <img src="<?= e(media_url($section['image_path'])) ?>" alt="<?= e($section['image_alt']) ?>" class="h-28 w-full rounded-xl object-cover">
                        <label class="consent-row mt-3"><input type="checkbox" name="remove_image" value="1"><span>Remove this image</span></label>
                    </div>
                <?php endif; ?>
                <div class="form-field"><label>Link label</label><input class="form-control" name="link_label" value="<?= e($section['link_label']) ?>"></div>
                <div class="form-field"><label>Link URL</label><input class="form-control" name="link_url" value="<?= e($section['link_url']) ?>"></div>
                <div class="form-field"><label>Status</label><select class="form-control" name="status"><option value="draft" <?= $section['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $section['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div>
            </div>
            <div class="admin-savebar"><button class="button button-primary">Save content</button></div>
        </form>
    </section>
</div>
