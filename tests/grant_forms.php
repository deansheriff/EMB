<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$form = query_one("SELECT * FROM grant_forms WHERE slug = 'fiyff-fertility-support-grant' LIMIT 1");
$fieldCount = $form ? (int) query_value('SELECT COUNT(*) FROM grant_form_fields WHERE form_id = ?', [$form['id']]) : 0;
$requiredEmail = $form ? (int) query_value(
    "SELECT COUNT(*) FROM grant_form_fields WHERE form_id = ? AND field_key = 'email' AND field_type = 'email' AND is_required = 1",
    [$form['id']]
) : 0;
$fileFields = $form ? (int) query_value(
    "SELECT COUNT(*) FROM grant_form_fields WHERE form_id = ? AND field_type = 'file'",
    [$form['id']]
) : 0;
$eventLink = (string) query_value("SELECT external_link FROM events WHERE slug = 'fiyff-fertility-support-grant' LIMIT 1");

$fixture = dirname(__DIR__) . '/tests/fixtures/sample.pdf';
$temporary = tempnam(sys_get_temp_dir(), 'emb-grant-test-');
copy($fixture, $temporary);
$validated = GrantDocumentUploader::validate([
    'name' => 'sample.pdf',
    'tmp_name' => $temporary,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($temporary),
]);
unlink($temporary);

$pathTraversalRejected = false;
try {
    GrantDocumentUploader::absolutePath('../.env');
} catch (RuntimeException) {
    $pathTraversalRejected = true;
}

$passed = $form !== null
    && $form['status'] === 'published'
    && grant_form_availability($form)['accepting'] === true
    && $fieldCount === 29
    && $requiredEmail === 1
    && $fileFields === 3
    && $eventLink === '/grants/fiyff-fertility-support-grant/apply'
    && $validated['mime'] === 'application/pdf'
    && $pathTraversalRejected;

echo json_encode([
    'passed' => $passed,
    'form' => $form['slug'] ?? null,
    'fields' => $fieldCount,
    'file_fields' => $fileFields,
    'private_path_traversal_rejected' => $pathTraversalRejected,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;

exit($passed ? 0 : 1);
