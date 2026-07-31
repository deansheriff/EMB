<?php
$title = $title ?? setting('default_meta_title', 'Emb Chronicles');
$description = $description ?? setting('default_meta_description', '');
$ogImage = $ogImage ?? '';
$canonical = url(ltrim(request_path(), '/'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(setting('site_name', 'Emb Chronicles')) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#6E3345">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php if ($ogImage): ?><meta property="og:image" content="<?= e(media_url($ogImage)) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body class="bg-ivory font-sans text-ink antialiased <?= e($bodyClass ?? '') ?>">
    <a href="#main-content" class="skip-link">Skip to content</a>
    <?php require BASE_PATH . '/views/partials/header.php'; ?>
    <?php require BASE_PATH . '/views/partials/flashes.php'; ?>
    <main id="main-content"><?= $content ?></main>
    <?php require BASE_PATH . '/views/partials/footer.php'; ?>
</body>
</html>

