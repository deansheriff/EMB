<?php
declare(strict_types=1);

final class MediaUploader
{
    private const MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function preferOriginal(string $path): string
    {
        $path = trim($path);
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $path), '/');
        if (!preg_match('#^/uploads/(\d{4}/\d{2})/([a-f0-9]{24})-(?:480|960|1440)\.webp$#i', $normalized, $matches)) {
            return $path;
        }

        foreach (self::MIME_MAP as $extension) {
            $relative = 'uploads/' . $matches[1] . '/' . $matches[2] . '-original.' . $extension;
            if (is_file(PUBLIC_PATH . '/' . $relative)) {
                return '/' . $relative;
            }
        }

        return $path;
    }

    public static function store(array $file, string $altText = ''): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Please choose a valid image.');
        }

        $maxBytes = (int) env('UPLOAD_MAX_MB', 8) * 1024 * 1024;
        if (($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('Image exceeds the ' . env('UPLOAD_MAX_MB', 8) . ' MB limit.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset(self::MIME_MAP[$mime])) {
            throw new RuntimeException('Only JPG, PNG, and WebP images are accepted.');
        }

        $dimensions = getimagesize($file['tmp_name']);
        if (!$dimensions || $dimensions[0] < 320 || $dimensions[1] < 240) {
            throw new RuntimeException('Image must be at least 320 × 240 pixels.');
        }

        $relativeDir = 'uploads/' . date('Y/m');
        $directory = PUBLIC_PATH . '/' . $relativeDir;
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the media directory.');
        }

        $base = bin2hex(random_bytes(12));
        $extension = self::MIME_MAP[$mime];
        $relativeOriginal = $relativeDir . '/' . $base . '-original.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], PUBLIC_PATH . '/' . $relativeOriginal)) {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        $variants = ['original' => '/' . $relativeOriginal];
        if (extension_loaded('gd')) {
            foreach ([480, 960, 1440] as $width) {
                if ($dimensions[0] <= $width) {
                    continue;
                }
                $variant = self::resize(PUBLIC_PATH . '/' . $relativeOriginal, $mime, $width, $directory, $base);
                if ($variant) {
                    $variants[(string) $width] = '/' . $relativeDir . '/' . basename($variant);
                }
            }
        }

        return [
            'path' => $variants['960'] ?? $variants['480'] ?? $variants['1440'] ?? $variants['original'],
            'variants' => $variants,
            'alt_text' => trim($altText),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'mime' => $mime,
        ];
    }

    private static function resize(string $sourcePath, string $mime, int $targetWidth, string $directory, string $base): ?string
    {
        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false,
        };
        if (!$source) {
            return null;
        }
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        // A true-colour GD canvas starts with opaque black pixels. Clear it to
        // full transparency before resampling so PNG and WebP logos keep their
        // transparent background in the generated WebP variants.
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        $target = $directory . '/' . $base . '-' . $targetWidth . '.webp';
        $saved = imagewebp($canvas, $target, 82);
        imagedestroy($canvas);
        imagedestroy($source);
        return $saved ? $target : null;
    }
}
