<header class="admin-page-header">
    <div><p class="text-sm text-muted">Portable backups and bulk updates</p><h1>Import and export</h1></div>
</header>

<div class="rounded-2xl border border-amber/25 bg-[#FFF7EB] p-5 text-sm leading-6 text-muted">
    Export a fresh CSV before making bulk changes. Imports update matching records by slug, email, client name, or applicant code and create records that do not exist. The entire import is rolled back if any row is invalid.
</div>

<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <?php foreach ($datasets as $key => $dataset): ?>
        <article class="admin-card">
            <div class="admin-card-header"><h2><?= e($dataset['label']) ?></h2><a class="button button-secondary" href="<?= e(url('/admin/data-tools?action=export&type=' . rawurlencode($key))) ?>">Export CSV</a></div>
            <p class="mt-4 text-sm leading-6 text-muted"><?= e($dataset['description']) ?></p>
            <details class="mt-5 rounded-xl bg-ivory p-4"><summary class="cursor-pointer text-sm font-bold text-wine">Required CSV columns</summary><code class="mt-3 block whitespace-normal break-words text-xs leading-6 text-muted"><?= e(implode(', ', $dataset['headers'])) ?></code></details>
            <form method="post" enctype="multipart/form-data" action="<?= e(url('/admin/data-tools')) ?>" class="mt-6 space-y-4">
                <?= csrf_field() ?><input type="hidden" name="type" value="<?= e($key) ?>">
                <div class="form-field"><label>Import <?= e(strtolower($dataset['label'])) ?> CSV</label><input class="file-control" type="file" name="csv_file" accept=".csv,text/csv" required><p class="field-help">Maximum 5 MB. Keep the exported header names unchanged.</p></div>
                <button class="button button-primary" data-confirm="Import this CSV and update matching records?">Import CSV</button>
            </form>
        </article>
    <?php endforeach; ?>
</section>
