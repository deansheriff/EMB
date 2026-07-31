<?php
declare(strict_types=1);

final class GrantDocumentUploader
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    public static function validate(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Choose a valid file.');
        }

        $maxBytes = (int) env('UPLOAD_MAX_MB', 8) * 1024 * 1024;
        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > $maxBytes) {
            throw new RuntimeException('Each document must be smaller than ' . env('UPLOAD_MAX_MB', 8) . ' MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file((string) $file['tmp_name']);
        if (!isset(self::MIME_EXTENSIONS[$mime])) {
            throw new RuntimeException('Only JPG, PNG, and PDF documents are accepted.');
        }

        $originalName = mb_substr(basename((string) ($file['name'] ?? 'document')), 0, 255);
        $originalName = preg_replace('/[\x00-\x1F\x7F"\\\\]+/u', '_', $originalName) ?: 'document.' . self::MIME_EXTENSIONS[$mime];

        return [
            'mime' => $mime,
            'extension' => self::MIME_EXTENSIONS[$mime],
            'size' => $size,
            'original_name' => $originalName,
        ];
    }

    public static function store(array $file, array $validated): string
    {
        $relativeDirectory = 'grant-documents/' . date('Y/m');
        $directory = BASE_PATH . '/storage/' . $relativeDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('The secure document directory could not be created.');
        }

        $relativePath = $relativeDirectory . '/' . bin2hex(random_bytes(20)) . '.' . $validated['extension'];
        if (!move_uploaded_file((string) $file['tmp_name'], BASE_PATH . '/storage/' . $relativePath)) {
            throw new RuntimeException('The document could not be stored.');
        }
        return $relativePath;
    }

    public static function absolutePath(string $relativePath): string
    {
        $base = realpath(BASE_PATH . '/storage/grant-documents');
        $path = realpath(BASE_PATH . '/storage/' . ltrim($relativePath, '/\\'));
        if ($base === false || $path === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Document not found.');
        }
        return $path;
    }
}
