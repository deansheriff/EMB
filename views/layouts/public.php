<?php
$title = $title ?? setting('default_meta_title', 'Emb Chronicles');
$description = trim((string) ($description ?? setting('default_meta_description', '')));
$ogImage = trim((string) ($ogImage ?? ''));
$ogImage = $ogImage !== '' ? $ogImage : trim((string) setting('social_share_image', ''));
$ogImageAlt = trim((string) ($ogImageAlt ?? ''));
$ogImageAlt = $ogImageAlt !== '' ? $ogImageAlt : trim((string) setting('social_share_image_alt', ''));
$siteName = (string) setting('site_name', 'Emb Chronicles');
$documentTitle = stripos((string) $title, $siteName) !== false ? (string) $title : $title . ' | ' . $siteName;
$canonical = url(ltrim(request_path(), '/'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($documentTitle) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#6E3345">
    <link rel="canonical" href="<?= e($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($documentTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <?php if ($ogImage): ?>
        <meta property="og:image" content="<?= e(media_url($ogImage)) ?>">
        <meta property="og:image:secure_url" content="<?= e(media_url($ogImage)) ?>">
        <?php if ($ogImageAlt): ?><meta property="og:image:alt" content="<?= e($ogImageAlt) ?>"><?php endif; ?>
    <?php endif; ?>
    <meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= e($documentTitle) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <?php if ($ogImage): ?>
        <meta name="twitter:image" content="<?= e(media_url($ogImage)) ?>">
        <?php if ($ogImageAlt): ?><meta name="twitter:image:alt" content="<?= e($ogImageAlt) ?>"><?php endif; ?>
    <?php endif; ?>
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
