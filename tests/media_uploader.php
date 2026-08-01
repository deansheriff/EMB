<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

if (!extension_loaded('gd') || !function_exists('imagewebp') || !(imagetypes() & IMG_WEBP)) {
    echo json_encode(['passed' => true, 'skipped' => 'GD WebP support is unavailable'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

$testDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'emb-media-' . bin2hex(random_bytes(6));
if (!mkdir($testDirectory, 0700, true) && !is_dir($testDirectory)) {
    throw new RuntimeException('Unable to create the media test directory.');
}

$sourcePath = $testDirectory . DIRECTORY_SEPARATOR . 'transparent-logo.png';
$source = imagecreatetruecolor(640, 320);
imagealphablending($source, false);
imagesavealpha($source, true);
$transparent = imagecolorallocatealpha($source, 0, 0, 0, 127);
imagefilledrectangle($source, 0, 0, 640, 320, $transparent);
$brandColour = imagecolorallocatealpha($source, 154, 79, 98, 0);
imagefilledellipse($source, 320, 160, 260, 180, $brandColour);
imagepng($source, $sourcePath);
imagedestroy($source);

$outputPath = null;
$publicOriginalPath = null;
try {
    $resize = new ReflectionMethod(MediaUploader::class, 'resize');
    $resize->setAccessible(true);
    $outputPath = $resize->invoke(null, $sourcePath, 'image/png', 320, $testDirectory, 'logo');
    if (!$outputPath || !is_file($outputPath)) {
        throw new RuntimeException('The resized WebP was not created.');
    }

    $resized = imagecreatefromwebp($outputPath);
    if (!$resized) {
        throw new RuntimeException('The resized WebP could not be opened.');
    }
    $cornerAlpha = (imagecolorat($resized, 0, 0) >> 24) & 0x7F;
    $centreAlpha = (imagecolorat($resized, 160, 80) >> 24) & 0x7F;
    imagedestroy($resized);

    if ($cornerAlpha < 120 || $centreAlpha > 10) {
        throw new RuntimeException('Transparency was not preserved during resizing.');
    }

    $uploadDirectory = PUBLIC_PATH . '/uploads/' . date('Y/m');
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Unable to create the upload compatibility test directory.');
    }
    $publicBase = bin2hex(random_bytes(12));
    $publicOriginalPath = $uploadDirectory . '/' . $publicBase . '-original.png';
    if (!copy($sourcePath, $publicOriginalPath)) {
        throw new RuntimeException('Unable to create the original-logo compatibility fixture.');
    }
    $legacyVariant = '/uploads/' . date('Y/m') . '/' . $publicBase . '-480.webp';
    $resolvedOriginal = MediaUploader::preferOriginal($legacyVariant);
    $expectedOriginal = '/uploads/' . date('Y/m') . '/' . $publicBase . '-original.png';
    if ($resolvedOriginal !== $expectedOriginal) {
        throw new RuntimeException('A legacy logo variant did not resolve to its preserved original.');
    }

    echo json_encode([
        'passed' => true,
        'transparent_corner_alpha' => $cornerAlpha,
        'opaque_centre_alpha' => $centreAlpha,
        'legacy_logo_original_resolved' => true,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
} finally {
    foreach ([$outputPath, $sourcePath, $publicOriginalPath] as $path) {
        if (is_string($path) && is_file($path)) {
            unlink($path);
        }
    }
    if (is_dir($testDirectory)) {
        rmdir($testDirectory);
    }
}
