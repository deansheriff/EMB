<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$migrationSql = (string) file_get_contents(dirname(__DIR__) . '/database/migrations/20261019_content_features.sql');
if (preg_match('/ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS/i', $migrationSql)) {
    throw new RuntimeException('The content migration uses ADD COLUMN IF NOT EXISTS, which is not supported by MySQL.');
}

$requiredBlogColumns = ['title', 'slug', 'excerpt', 'content', 'status', 'published_at'];
$blogColumns = query_all(
    "SELECT column_name FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'blog_posts'"
);
$blogColumnNames = array_column($blogColumns, 'column_name');
if (array_diff($requiredBlogColumns, $blogColumnNames)) {
    throw new RuntimeException('The blog schema is incomplete.');
}

$requiredTestimonialColumns = ['submitter_email', 'source', 'approval_status', 'consented_at', 'reviewed_by', 'reviewed_at'];
$testimonialColumns = query_all(
    "SELECT column_name FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'testimonials'"
);
$testimonialColumnNames = array_column($testimonialColumns, 'column_name');
if (array_diff($requiredTestimonialColumns, $testimonialColumnNames)) {
    throw new RuntimeException('The testimonial moderation schema is incomplete.');
}

if ((int) query_value("SELECT COUNT(*) FROM permissions WHERE slug = 'blog.manage'") !== 1) {
    throw new RuntimeException('The blog management permission is missing.');
}
if ((string) setting('testimonial_submissions_enabled', '') !== '1') {
    throw new RuntimeException('Public testimonial submissions should be enabled by default.');
}

$pdo = db();
$pdo->beginTransaction();
try {
    $suffix = bin2hex(random_bytes(4));
    $pdo->prepare(
        "INSERT INTO blog_posts (title, slug, excerpt, content, status, published_at)
         VALUES (?, ?, ?, ?, 'published', NOW())"
    )->execute(['QA Blog ' . $suffix, 'qa-blog-' . $suffix, 'QA excerpt.', '<p>QA article.</p>']);
    if (!query_one("SELECT id FROM blog_posts WHERE slug = ? AND status = 'published'", ['qa-blog-' . $suffix])) {
        throw new RuntimeException('A published blog post could not be stored.');
    }

    $pdo->prepare(
        "INSERT INTO testimonials (client_name, submitter_email, quote, source, approval_status, consented_at, is_visible)
         VALUES (?, ?, ?, 'public', 'pending', NOW(), 0)"
    )->execute(['QA Contributor', 'qa-' . $suffix . '@example.com', 'A temporary testimonial used to verify moderation.']);
    $testimonialId = (int) $pdo->lastInsertId();
    $pending = query_one('SELECT approval_status, is_visible FROM testimonials WHERE id = ?', [$testimonialId]);
    if (!$pending || $pending['approval_status'] !== 'pending' || (int) $pending['is_visible'] !== 0) {
        throw new RuntimeException('A public testimonial did not remain pending and hidden.');
    }

    $eventId = (int) query_value('SELECT id FROM events ORDER BY id LIMIT 1');
    if ($eventId > 0) {
        $pdo->prepare('INSERT INTO event_media (event_id, image_path, alt_text, sort_order) VALUES (?, ?, ?, ?), (?, ?, ?, ?)')
            ->execute([$eventId, '/uploads/qa-gallery-1.webp', 'QA gallery image one', 9001, $eventId, '/uploads/qa-gallery-2.webp', 'QA gallery image two', 9002]);
        $galleryCount = (int) query_value('SELECT COUNT(*) FROM event_media WHERE event_id = ? AND sort_order IN (9001, 9002)', [$eventId]);
        if ($galleryCount !== 2) {
            throw new RuntimeException('An event could not retain multiple gallery images.');
        }
    }

    $pdo->rollBack();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

echo json_encode([
    'passed' => true,
    'blog_schema' => true,
    'testimonial_moderation' => true,
    'event_gallery' => true,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
