<?php
declare(strict_types=1);

function data_transfer_catalog(): array
{
    return [
        'services' => [
            'label' => 'Services',
            'permission' => 'services.manage',
            'description' => 'Titles, descriptions, covers, publishing status, ordering, and SEO fields.',
            'headers' => ['title', 'slug', 'excerpt', 'description', 'cover_image', 'cover_alt', 'sort_order', 'is_pinned', 'status', 'seo_title', 'seo_description'],
        ],
        'events' => [
            'label' => 'Events',
            'permission' => 'events.manage',
            'description' => 'Event details, schedule, location, cover, featured status, and SEO fields.',
            'headers' => ['title', 'slug', 'excerpt', 'description', 'event_date', 'event_end', 'timezone', 'location_mode', 'location', 'event_type', 'external_link', 'cover_image', 'cover_alt', 'is_featured', 'status', 'seo_title', 'seo_description'],
        ],
        'testimonials' => [
            'label' => 'Testimonials',
            'permission' => 'testimonials.manage',
            'description' => 'Client names, quotes, photos, visibility, and ordering.',
            'headers' => ['client_name', 'photo_path', 'photo_alt', 'quote', 'sort_order', 'is_visible'],
        ],
        'subscribers' => [
            'label' => 'Newsletter subscribers',
            'permission' => 'contacts.manage',
            'description' => 'Consented newsletter email addresses and consent dates.',
            'headers' => ['email', 'consented_at', 'created_at'],
        ],
        'applications' => [
            'label' => 'Grant applications',
            'permission' => 'grants.manage',
            'description' => 'Applicant records and structured answers. Protected documents are never included.',
            'headers' => ['applicant_code', 'event_slug', 'form_slug', 'full_name', 'email', 'phone', 'location', 'answers_json', 'eligibility_complete', 'status', 'internal_notes', 'consented_at', 'created_at'],
        ],
    ];
}

function data_transfer_definition(string $type): array
{
    $catalog = data_transfer_catalog();
    if (!isset($catalog[$type])) {
        throw new RuntimeException('Choose a supported data type.');
    }
    if (!can($catalog[$type]['permission'])) {
        throw new RuntimeException('You do not have permission to transfer this data.');
    }
    return $catalog[$type];
}

function export_data_csv(string $type): never
{
    $definition = data_transfer_definition($type);
    $rows = match ($type) {
        'services' => query_all('SELECT title, slug, excerpt, description, cover_image, cover_alt, sort_order, is_pinned, status, seo_title, seo_description FROM services ORDER BY sort_order, id'),
        'events' => query_all('SELECT title, slug, excerpt, description, event_date, event_end, timezone, location_mode, location, event_type, external_link, cover_image, cover_alt, is_featured, status, seo_title, seo_description FROM events ORDER BY event_date, id'),
        'testimonials' => query_all('SELECT client_name, photo_path, photo_alt, quote, sort_order, is_visible FROM testimonials ORDER BY sort_order, id'),
        'subscribers' => query_all('SELECT email, consented_at, created_at FROM newsletter_subscribers ORDER BY created_at DESC'),
        'applications' => query_all(
            'SELECT ga.applicant_code, e.slug AS event_slug, gf.slug AS form_slug, ga.full_name, ga.email, ga.phone, ga.location,
                    ga.answers_json, ga.eligibility_complete, ga.status, ga.internal_notes, ga.consented_at, ga.created_at
             FROM grant_applications ga
             JOIN events e ON e.id = ga.event_id
             LEFT JOIN grant_forms gf ON gf.id = ga.form_id
             ORDER BY ga.created_at DESC'
        ),
    };

    $filename = 'emb-' . $type . '-' . date('Y-m-d-His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, max-age=0');
    $output = fopen('php://output', 'wb');
    if ($output === false) {
        throw new RuntimeException('Unable to create the CSV export.');
    }
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $definition['headers']);
    foreach ($rows as $row) {
        fputcsv($output, array_map('csv_export_value', array_values($row)));
    }
    fclose($output);
    exit;
}

function csv_export_value(mixed $value): string
{
    $value = (string) ($value ?? '');
    return preg_match('/^[=+\-@]/', ltrim($value)) ? "'" . $value : $value;
}

function import_data_csv(string $type, ?array $file): int
{
    $definition = data_transfer_definition($type);
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Choose a CSV file to import.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('CSV imports must be 5 MB or smaller.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    if (!in_array($mime, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'], true)) {
        throw new RuntimeException('Upload a valid CSV file.');
    }
    $handle = fopen((string) $file['tmp_name'], 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to read the CSV file.');
    }
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        throw new RuntimeException('The CSV file is empty.');
    }
    $headers = array_map(static function (string $header): string {
        return strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header));
    }, $headers);
    $missing = array_diff($definition['headers'], $headers);
    if ($missing) {
        fclose($handle);
        throw new RuntimeException('Missing CSV columns: ' . implode(', ', $missing) . '. Export a fresh template and keep its header row.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    $count = 0;
    $line = 1;
    try {
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, static fn ($value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $values = array_pad($values, count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));
            if ($row === false) {
                throw new RuntimeException('Unable to read row ' . $line . '.');
            }
            import_data_row($type, array_map(static fn ($value): string => trim((string) $value), $row), $line);
            $count++;
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fclose($handle);
        throw $exception;
    }
    fclose($handle);
    return $count;
}

function import_data_row(string $type, array $row, int $line): void
{
    switch ($type) {
        case 'services': import_service_row($row, $line); break;
        case 'events': import_event_row($row, $line); break;
        case 'testimonials': import_testimonial_row($row, $line); break;
        case 'subscribers': import_subscriber_row($row, $line); break;
        case 'applications': import_application_row($row, $line); break;
        default: throw new RuntimeException('Unsupported data type.');
    }
}

function import_service_row(array $row, int $line): void
{
    csv_require($row, ['title', 'excerpt', 'description'], $line);
    $slug = slugify($row['slug'] ?: $row['title']);
    $status = in_array($row['status'], ['draft', 'published'], true) ? $row['status'] : 'draft';
    db()->prepare(
        'INSERT INTO services (title, slug, excerpt, description, cover_image, cover_alt, sort_order, is_pinned, status, seo_title, seo_description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE title=VALUES(title), excerpt=VALUES(excerpt), description=VALUES(description), cover_image=VALUES(cover_image), cover_alt=VALUES(cover_alt), sort_order=VALUES(sort_order), is_pinned=VALUES(is_pinned), status=VALUES(status), seo_title=VALUES(seo_title), seo_description=VALUES(seo_description)'
    )->execute([$row['title'], $slug, $row['excerpt'], $row['description'], $row['cover_image'], $row['cover_alt'], (int) $row['sort_order'], csv_bool($row['is_pinned']), $status, $row['seo_title'], $row['seo_description']]);
}

function import_event_row(array $row, int $line): void
{
    csv_require($row, ['title', 'excerpt', 'description', 'event_type'], $line);
    $slug = slugify($row['slug'] ?: $row['title']);
    $status = in_array($row['status'], ['draft', 'published'], true) ? $row['status'] : 'draft';
    $mode = in_array($row['location_mode'], ['physical', 'online', 'hybrid'], true) ? $row['location_mode'] : 'physical';
    db()->prepare(
        'INSERT INTO events (title, slug, excerpt, description, event_date, event_end, timezone, location_mode, location, event_type, external_link, cover_image, cover_alt, is_featured, status, seo_title, seo_description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE title=VALUES(title), excerpt=VALUES(excerpt), description=VALUES(description), event_date=VALUES(event_date), event_end=VALUES(event_end), timezone=VALUES(timezone), location_mode=VALUES(location_mode), location=VALUES(location), event_type=VALUES(event_type), external_link=VALUES(external_link), cover_image=VALUES(cover_image), cover_alt=VALUES(cover_alt), is_featured=VALUES(is_featured), status=VALUES(status), seo_title=VALUES(seo_title), seo_description=VALUES(seo_description)'
    )->execute([$row['title'], $slug, $row['excerpt'], $row['description'], csv_datetime($row['event_date'], $line, true), csv_datetime($row['event_end'], $line, true), $row['timezone'] ?: 'Africa/Lagos', $mode, $row['location'], $row['event_type'], $row['external_link'], $row['cover_image'], $row['cover_alt'], csv_bool($row['is_featured']), $status, $row['seo_title'], $row['seo_description']]);
}

function import_testimonial_row(array $row, int $line): void
{
    csv_require($row, ['client_name', 'quote'], $line);
    db()->prepare(
        'INSERT INTO testimonials (client_name, photo_path, photo_alt, quote, sort_order, is_visible)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE photo_path=VALUES(photo_path), photo_alt=VALUES(photo_alt), quote=VALUES(quote), sort_order=VALUES(sort_order), is_visible=VALUES(is_visible)'
    )->execute([$row['client_name'], $row['photo_path'], $row['photo_alt'], $row['quote'], (int) $row['sort_order'], csv_bool($row['is_visible'])]);
}

function import_subscriber_row(array $row, int $line): void
{
    if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Row ' . $line . ' contains an invalid subscriber email address.');
    }
    $consentedAt = csv_datetime($row['consented_at'], $line) ?: date('Y-m-d H:i:s');
    db()->prepare('INSERT INTO newsletter_subscribers (email, consented_at) VALUES (?, ?) ON DUPLICATE KEY UPDATE consented_at=VALUES(consented_at)')
        ->execute([strtolower($row['email']), $consentedAt]);
}

function import_application_row(array $row, int $line): void
{
    csv_require($row, ['applicant_code', 'event_slug', 'full_name', 'email', 'phone', 'location'], $line);
    if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Row ' . $line . ' contains an invalid applicant email address.');
    }
    $eventId = query_value('SELECT id FROM events WHERE slug = ? LIMIT 1', [$row['event_slug']]);
    if (!$eventId) {
        throw new RuntimeException('Row ' . $line . ' references an event slug that does not exist.');
    }
    $formId = null;
    if ($row['form_slug'] !== '') {
        $formId = query_value('SELECT id FROM grant_forms WHERE slug = ? LIMIT 1', [$row['form_slug']]);
        if (!$formId) {
            throw new RuntimeException('Row ' . $line . ' references a grant form slug that does not exist.');
        }
    }
    $answersJson = $row['answers_json'] ?: '{}';
    try {
        json_decode($answersJson, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        throw new RuntimeException('Row ' . $line . ' contains invalid answers_json.');
    }
    $status = in_array($row['status'], ['submitted', 'in_review', 'shortlisted', 'declined', 'awarded'], true) ? $row['status'] : 'submitted';
    $consentedAt = csv_datetime($row['consented_at'], $line) ?: date('Y-m-d H:i:s');
    db()->prepare(
        'INSERT INTO grant_applications (event_id, form_id, applicant_code, full_name, email, phone, location, answers_json, eligibility_complete, status, internal_notes, consented_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE event_id=VALUES(event_id), form_id=VALUES(form_id), full_name=VALUES(full_name), email=VALUES(email), phone=VALUES(phone), location=VALUES(location), answers_json=VALUES(answers_json), eligibility_complete=VALUES(eligibility_complete), status=VALUES(status), internal_notes=VALUES(internal_notes), consented_at=VALUES(consented_at)'
    )->execute([(int) $eventId, $formId ? (int) $formId : null, $row['applicant_code'], $row['full_name'], strtolower($row['email']), $row['phone'], $row['location'], $answersJson, csv_bool($row['eligibility_complete']), $status, $row['internal_notes'], $consentedAt]);
}

function csv_require(array $row, array $fields, int $line): void
{
    foreach ($fields as $field) {
        if (($row[$field] ?? '') === '') {
            throw new RuntimeException('Row ' . $line . ' is missing the required ' . $field . ' value.');
        }
    }
}

function csv_bool(string $value): int
{
    return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
}

function csv_datetime(string $value, int $line, bool $nullable = false): ?string
{
    if ($value === '') {
        return $nullable ? null : null;
    }
    try {
        return (new DateTimeImmutable($value))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        throw new RuntimeException('Row ' . $line . ' contains an invalid date or time.');
    }
}
