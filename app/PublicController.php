<?php
declare(strict_types=1);

function public_dispatch(string $path): void
{
    if ($path === '/health') {
        public_health();
        return;
    }

    if ($path === '/payments/paystack/webhook' && is_post()) {
        handle_paystack_webhook();
        return;
    }

    if (is_post()) {
        if ($path === '/contact') {
            submit_contact();
            return;
        }
        if ($path === '/appointment') {
            submit_appointment();
            return;
        }
        if ($path === '/newsletter') {
            submit_newsletter();
            return;
        }
        if (preg_match('#^/appointment/pay/([A-Z0-9-]+)$#i', $path, $matches)) {
            retry_appointment_payment($matches[1]);
            return;
        }
        if (preg_match('#^/grant-application/([a-z0-9-]+)$#', $path, $matches)) {
            submit_grant_application($matches[1]);
            return;
        }
        if (preg_match('#^/grants/([a-z0-9-]+)/apply$#', $path, $matches)) {
            submit_managed_grant_application($matches[1]);
            return;
        }
    }

    switch ($path) {
        case '/':
            $heroes = query_all('SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order, id');
            $events = query_all("SELECT * FROM events WHERE status = 'published' AND is_featured = 1 ORDER BY event_date ASC LIMIT 3");
            $services = query_all("SELECT * FROM services WHERE status = 'published' ORDER BY is_pinned DESC, sort_order, created_at DESC LIMIT 3");
            $testimonials = query_all('SELECT * FROM testimonials WHERE is_visible = 1 ORDER BY sort_order, id');
            render('public/home', compact('heroes', 'events', 'services', 'testimonials') + [
                'title' => 'Fertility education with clarity and compassion',
                'description' => (string) setting('default_meta_description'),
                'bodyClass' => 'home-page',
            ]);
            return;

        case '/about':
            render('public/about', [
                'title' => 'About Us',
                'description' => 'Meet Emb Chronicles and discover our approach to fertility education, advocacy, and community.',
            ]);
            return;

        case '/services':
            $services = query_all("SELECT * FROM services WHERE status = 'published' ORDER BY sort_order, created_at DESC");
            render('public/services', [
                'services' => $services,
                'title' => 'Fertility consultation and education services',
                'description' => 'Explore personal fertility consultations, IVF clarity, clinic guidance, and STEM career mentorship.',
            ]);
            return;

        case '/events':
            public_events_index();
            return;

        case '/fiyff-foundation':
            $grant = query_one("SELECT * FROM events WHERE status = 'published' AND event_type = 'Grant Program' ORDER BY event_date DESC LIMIT 1");
            render('public/fiyff', [
                'grant' => $grant,
                'title' => 'FIYFF Foundation',
                'description' => 'Awareness, advocacy, and fertility support from the Fatima Ibrahim Yakubu Fertility Foundation.',
            ]);
            return;

        case '/community':
            render('public/community', [
                'title' => 'STEM and TTC Communities',
                'description' => 'Support for the TTC community and career mentorship for life-science graduates entering ART.',
            ]);
            return;

        case '/contact':
            render('public/contact', [
                'title' => 'Contact Us',
                'description' => 'Ask a question or connect with Emb Chronicles by form, phone, or WhatsApp.',
            ]);
            clear_old();
            return;

        case '/appointment':
            $fee = money_to_subunit((string) setting('appointment_fee', '0'));
            render('public/appointment', [
                'title' => 'Book a Session',
                'description' => 'Book a fertility education, clinic guidance, IVF clarity, or STEM career consultation.',
                'paymentRequired' => PaystackClient::configured(),
                'appointmentFee' => $fee,
                'currency' => strtoupper((string) setting('paystack_currency', 'NGN')),
            ]);
            clear_old();
            return;

        case '/payments/paystack/callback':
            handle_paystack_callback();
            return;

        case '/privacy':
            render('public/privacy', [
                'title' => 'Privacy Policy',
                'description' => 'How Emb Chronicles handles information submitted through the website.',
            ]);
            return;
    }

    if (preg_match('#^/services/([a-z0-9-]+)$#', $path, $matches)) {
        $service = query_one("SELECT * FROM services WHERE slug = ? AND status = 'published' LIMIT 1", [$matches[1]]);
        if (!$service) {
            public_not_found();
            return;
        }
        $gallery = query_all('SELECT * FROM service_media WHERE service_id = ? ORDER BY sort_order, id', [$service['id']]);
        $related = query_all("SELECT * FROM services WHERE status = 'published' AND id <> ? ORDER BY is_pinned DESC, sort_order LIMIT 3", [$service['id']]);
        render('public/service-detail', [
            'service' => $service,
            'gallery' => $gallery,
            'related' => $related,
            'title' => $service['seo_title'] ?: $service['title'],
            'description' => $service['seo_description'] ?: $service['excerpt'],
            'ogImage' => $service['cover_image'],
        ]);
        return;
    }

    if (preg_match('#^/events/([a-z0-9-]+)$#', $path, $matches)) {
        $event = query_one("SELECT * FROM events WHERE slug = ? AND status = 'published' LIMIT 1", [$matches[1]]);
        if (!$event) {
            public_not_found();
            return;
        }
        $gallery = query_all('SELECT * FROM event_media WHERE event_id = ? ORDER BY sort_order, id', [$event['id']]);
        $related = query_all("SELECT * FROM events WHERE status = 'published' AND id <> ? ORDER BY event_date ASC LIMIT 3", [$event['id']]);
        render('public/event-detail', [
            'event' => $event,
            'gallery' => $gallery,
            'related' => $related,
            'title' => $event['seo_title'] ?: $event['title'],
            'description' => $event['seo_description'] ?: $event['excerpt'],
            'ogImage' => $event['cover_image'],
        ]);
        return;
    }

    if (preg_match('#^/grant-application/([a-z0-9-]+)$#', $path, $matches)) {
        $managedForm = query_one("SELECT slug FROM grant_forms WHERE slug = ? AND status IN ('published','closed') LIMIT 1", [$matches[1]]);
        if ($managedForm) {
            redirect('/grants/' . $managedForm['slug'] . '/apply');
        }
        $event = query_one("SELECT * FROM events WHERE slug = ? AND status = 'published' AND event_type = 'Grant Program' LIMIT 1", [$matches[1]]);
        if (!$event) {
            public_not_found();
            return;
        }
        render('public/grant-application', [
            'event' => $event,
            'title' => 'Apply — ' . $event['title'],
            'description' => 'Submit a secure application for ' . $event['title'] . '.',
        ]);
        clear_old();
        return;
    }

    if (preg_match('#^/grants/([a-z0-9-]+)/apply$#', $path, $matches)) {
        show_managed_grant_application($matches[1]);
        return;
    }

    if (preg_match('#^/appointment/status/([A-Z0-9-]+)$#i', $path, $matches)) {
        show_appointment_status($matches[1]);
        return;
    }

    public_not_found();
}

function public_health(): void
{
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    try {
        query_value('SELECT 1');
        echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        http_response_code(503);
        echo json_encode(['status' => 'unavailable'], JSON_THROW_ON_ERROR);
    }
}

function public_events_index(): void
{
    $where = ["status = 'published'"];
    $params = [];
    $type = trim((string) ($_GET['type'] ?? ''));
    $search = trim((string) ($_GET['q'] ?? ''));
    if ($type !== '') {
        $where[] = 'event_type = ?';
        $params[] = $type;
    }
    if ($search !== '') {
        $where[] = '(title LIKE ? OR excerpt LIKE ? OR location LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term);
    }
    $events = query_all(
        'SELECT * FROM events WHERE ' . implode(' AND ', $where) . ' ORDER BY (event_date IS NULL), event_date ASC, created_at DESC',
        $params
    );
    $types = query_all("SELECT DISTINCT event_type FROM events WHERE status = 'published' ORDER BY event_type");
    render('public/events', [
        'events' => $events,
        'types' => $types,
        'activeType' => $type,
        'search' => $search,
        'title' => 'Events and Opportunities',
        'description' => 'Discover fertility education sessions, community events, STEM workshops, and FIYFF grant programs.',
    ]);
}

function submit_contact(): void
{
    verify_csrf();
    if (!is_honeypot_clean()) {
        redirect('/contact?sent=1');
    }
    if (form_rate_limited('contact')) {
        flash('error', 'Please wait a moment before submitting another message.');
        redirect('/contact');
    }
    $data = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'topic' => trim((string) ($_POST['topic'] ?? '')),
        'message' => trim((string) ($_POST['message'] ?? '')),
    ];
    store_old($_POST);
    if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['message']) < 15 || empty($_POST['consent'])) {
        flash('error', 'Please complete the required fields, use a valid email, and confirm consent.');
        redirect('/contact');
    }
    $stmt = db()->prepare('INSERT INTO contact_submissions (name, email, phone, topic, message, consented_at, source_page) VALUES (?, ?, ?, ?, ?, NOW(), ?)');
    $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['topic'], $data['message'], '/contact']);
    $contactId = (int) db()->lastInsertId();
    send_contact_confirmation($contactId, $data);
    clear_old();
    flash('success', 'Your message has been received. We will respond using the contact details you provided.');
    redirect('/contact?sent=1');
}

function submit_appointment(): void
{
    verify_csrf();
    if (!is_honeypot_clean()) {
        redirect('/appointment?sent=1');
    }
    if (form_rate_limited('appointment')) {
        flash('error', 'Please wait a moment before submitting another request.');
        redirect('/appointment');
    }
    store_old($_POST);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $required = ['consultation_type', 'name', 'phone', 'preferred_contact'];
    foreach ($required as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            flash('error', 'Please complete all required fields.');
            redirect('/appointment');
        }
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($_POST['consent'])) {
        flash('error', 'Please provide a valid email and confirm consent.');
        redirect('/appointment');
    }
    $bookingCode = new_booking_code();
    $currency = strtoupper((string) setting('paystack_currency', 'NGN'));
    $amount = PaystackClient::configured() ? money_to_subunit((string) setting('appointment_fee', '0')) : 0;
    $requiresPayment = $amount > 0;
    $status = $requiresPayment ? 'pending_payment' : 'new';
    $paymentStatus = $requiresPayment ? 'pending' : 'not_required';

    $stmt = db()->prepare(
        'INSERT INTO appointments
         (booking_code, consultation_type, preferred_date, preferred_time, name, email, phone, preferred_contact, message, status, amount_due, currency, payment_status, consented_at)
         VALUES (?, ?, NULLIF(?, ""), NULLIF(?, ""), ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $bookingCode,
        trim((string) $_POST['consultation_type']),
        trim((string) ($_POST['preferred_date'] ?? '')),
        trim((string) ($_POST['preferred_time'] ?? '')),
        trim((string) $_POST['name']),
        $email,
        trim((string) $_POST['phone']),
        trim((string) $_POST['preferred_contact']),
        trim((string) ($_POST['message'] ?? '')),
        $status,
        $amount,
        $currency,
        $paymentStatus,
    ]);
    $appointmentId = (int) db()->lastInsertId();
    clear_old();

    if ($requiresPayment) {
        try {
            $checkoutUrl = begin_appointment_payment($appointmentId);
            redirect($checkoutUrl);
        } catch (Throwable $exception) {
            db()->prepare("UPDATE appointments SET payment_status = 'failed' WHERE id = ?")->execute([$appointmentId]);
            flash('error', 'Your booking was saved, but secure checkout could not start. You can retry below.');
            redirect('/appointment/status/' . $bookingCode);
        }
    }

    $emailed = send_appointment_confirmation($appointmentId, false);
    flash('success', $emailed
        ? 'Your session request has been received and a confirmation email was sent.'
        : 'Your session request has been received. Keep the booking reference shown below.');
    redirect('/appointment/status/' . $bookingCode);
}

function submit_newsletter(): void
{
    verify_csrf();
    if (!is_honeypot_clean()) {
        redirect('/');
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a valid email address to join the list.');
    } else {
        $stmt = db()->prepare('INSERT INTO newsletter_subscribers (email, consented_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE consented_at = VALUES(consented_at)');
        $stmt->execute([$email]);
        if (Mailer::confirmationsEnabled()) {
            Mailer::send(
                $email,
                '',
                'You are on the Emb Chronicles community list',
                '<h1 style="color:#6e3345;margin-top:0">Welcome to the community</h1><p>Thank you for joining the Emb Chronicles update list. We will only send occasional fertility education, events, and community opportunities.</p>',
                'newsletter_welcome',
                'newsletter_subscriber',
                (int) db()->lastInsertId()
            );
        }
        flash('success', 'You are on the list for future community updates.');
    }
    redirect((string) ($_SERVER['HTTP_REFERER'] ?? url('/')));
}

function submit_grant_application(string $slug): void
{
    verify_csrf();
    $event = query_one("SELECT * FROM events WHERE slug = ? AND status = 'published' AND event_type = 'Grant Program' LIMIT 1", [$slug]);
    if (!$event) {
        public_not_found();
        return;
    }
    if (!is_honeypot_clean()) {
        redirect('/grant-application/' . $slug . '?sent=1');
    }
    if (form_rate_limited('grant', 60)) {
        flash('error', 'Please wait before submitting another application.');
        redirect('/grant-application/' . $slug);
    }
    store_old($_POST);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $fields = ['full_name', 'phone', 'location', 'journey_summary', 'support_need'];
    foreach ($fields as $field) {
        if (mb_strlen(trim((string) ($_POST[$field] ?? ''))) < ($field === 'journey_summary' ? 30 : 2)) {
            flash('error', 'Please complete each required application section.');
            redirect('/grant-application/' . $slug);
        }
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($_POST['eligibility']) || empty($_POST['consent'])) {
        flash('error', 'Confirm eligibility and consent, and provide a valid email.');
        redirect('/grant-application/' . $slug);
    }
    $code = 'FIYFF-' . strtoupper(bin2hex(random_bytes(4)));
    $answers = json_encode([
        'journey_summary' => trim((string) $_POST['journey_summary']),
        'support_need' => trim((string) $_POST['support_need']),
        'clinic_status' => trim((string) ($_POST['clinic_status'] ?? '')),
    ], JSON_THROW_ON_ERROR);
    $stmt = db()->prepare(
        'INSERT INTO grant_applications (event_id, applicant_code, full_name, email, phone, location, answers_json, eligibility_complete, consented_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())'
    );
    $stmt->execute([
        $event['id'], $code, trim((string) $_POST['full_name']), $email,
        trim((string) $_POST['phone']), trim((string) $_POST['location']), $answers,
    ]);
    $grantId = (int) db()->lastInsertId();
    send_grant_confirmation($grantId, $code, trim((string) $_POST['full_name']), $email, $event['title']);
    clear_old();
    $_SESSION['grant_code'] = $code;
    flash('success', 'Your application has been submitted securely. Reference: ' . $code);
    redirect('/grant-application/' . $slug . '?sent=1');
}

function show_managed_grant_application(string $slug): void
{
    $form = query_one(
        "SELECT gf.*, e.title AS event_title, e.cover_image
         FROM grant_forms gf
         LEFT JOIN events e ON e.id = gf.event_id
         WHERE gf.slug = ? AND gf.status IN ('published','closed')
         LIMIT 1",
        [$slug]
    );
    if (!$form) {
        public_not_found();
        return;
    }
    $fields = query_all('SELECT * FROM grant_form_fields WHERE form_id = ? ORDER BY sort_order, id', [$form['id']]);
    $sections = grant_form_sections($fields);
    $availability = grant_form_availability($form);
    render('public/managed-grant-application', [
        'form' => $form,
        'sections' => $sections,
        'availability' => $availability,
        'title' => $form['title'],
        'description' => excerpt($form['intro'], 240),
        'ogImage' => $form['cover_image'] ?? '',
        'bodyClass' => 'grant-application-page',
    ]);
    clear_old();
}

function submit_managed_grant_application(string $slug): void
{
    verify_csrf();
    $form = query_one(
        "SELECT gf.*, e.title AS event_title
         FROM grant_forms gf
         LEFT JOIN events e ON e.id = gf.event_id
         WHERE gf.slug = ? LIMIT 1",
        [$slug]
    );
    if (!$form) {
        public_not_found();
        return;
    }
    $availability = grant_form_availability($form);
    if (!$availability['accepting']) {
        flash('error', $availability['message']);
        redirect('/grants/' . $slug . '/apply');
    }
    if (!is_honeypot_clean()) {
        redirect('/grants/' . $slug . '/apply?sent=1');
    }
    if (form_rate_limited('managed_grant_' . $form['id'], 60)) {
        flash('error', 'Please wait before submitting another application.');
        redirect('/grants/' . $slug . '/apply');
    }

    $fields = query_all('SELECT * FROM grant_form_fields WHERE form_id = ? ORDER BY sort_order, id', [$form['id']]);
    if (!$fields) {
        flash('error', 'This application form is not ready yet.');
        redirect('/grants/' . $slug . '/apply');
    }

    store_old($_POST);
    $answers = [];
    $fileQueue = [];
    $errors = [];
    $totalUploadBytes = 0;
    foreach ($fields as $field) {
        $key = (string) $field['field_key'];
        $type = (string) $field['field_type'];
        $validation = json_decode((string) ($field['validation_json'] ?? ''), true) ?: [];
        if ($type === 'file') {
            $file = $_FILES[$key] ?? null;
            $hasFile = is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if (!$hasFile && (int) $field['is_required'] === 1) {
                $errors[] = $field['label'] . ' is required.';
                continue;
            }
            if ($hasFile) {
                try {
                    $validated = GrantDocumentUploader::validate($file);
                    $totalUploadBytes += (int) $validated['size'];
                    $fileQueue[] = ['field' => $field, 'file' => $file, 'validated' => $validated];
                } catch (RuntimeException $exception) {
                    $errors[] = $field['label'] . ': ' . $exception->getMessage();
                }
            }
            continue;
        }

        $rawValue = $_POST[$key] ?? '';
        $value = is_array($rawValue) ? implode(', ', array_map('trim', $rawValue)) : trim((string) $rawValue);
        if ((int) $field['is_required'] === 1 && $value === '') {
            $errors[] = $field['label'] . ' is required.';
            continue;
        }
        if ($value !== '' && $type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $field['label'] . ' must be a valid email address.';
        }
        if ($value !== '' && isset($validation['minlength']) && mb_strlen($value) < (int) $validation['minlength']) {
            $errors[] = $field['label'] . ' must be at least ' . (int) $validation['minlength'] . ' characters.';
        }
        if ($value !== '' && isset($validation['maxlength']) && mb_strlen($value) > (int) $validation['maxlength']) {
            $errors[] = $field['label'] . ' must be no more than ' . (int) $validation['maxlength'] . ' characters.';
        }
        if ($value !== '' && $type === 'number') {
            if (!is_numeric($value)) {
                $errors[] = $field['label'] . ' must be a number.';
            } elseif (isset($validation['min']) && (float) $value < (float) $validation['min']) {
                $errors[] = $field['label'] . ' is below the allowed minimum.';
            } elseif (isset($validation['max']) && (float) $value > (float) $validation['max']) {
                $errors[] = $field['label'] . ' is above the allowed maximum.';
            }
        }
        $options = json_decode((string) ($field['options_json'] ?? ''), true) ?: [];
        if ($value !== '' && in_array($type, ['select', 'radio'], true) && $options && !in_array($value, $options, true)) {
            $errors[] = $field['label'] . ' contains an invalid selection.';
        }
        $answers[$key] = $value;
    }

    $maxTotal = (int) env('GRANT_UPLOAD_TOTAL_MB', 18) * 1024 * 1024;
    if ($totalUploadBytes > $maxTotal) {
        $errors[] = 'Combined documents must be smaller than ' . env('GRANT_UPLOAD_TOTAL_MB', 18) . ' MB.';
    }
    if (empty($_POST['accuracy']) || empty($_POST['consent'])) {
        $errors[] = 'Confirm the information declaration and data-processing consent.';
    }
    if ($errors) {
        flash('error', implode(' ', $errors));
        redirect('/grants/' . $slug . '/apply');
    }

    $email = strtolower((string) ($answers['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'The form must include a valid applicant email address.');
        redirect('/grants/' . $slug . '/apply');
    }
    $fullName = trim(implode(' ', array_filter([
        $answers['prefix'] ?? '',
        $answers['first_name'] ?? '',
        $answers['last_name'] ?? '',
    ]))) ?: trim((string) ($answers['full_name'] ?? 'Applicant'));
    $location = trim(implode(', ', array_filter([
        $answers['address_city'] ?? '',
        $answers['address_state'] ?? '',
        $answers['address_country'] ?? '',
    ]))) ?: trim((string) ($answers['location'] ?? 'Not provided'));
    $phone = trim((string) ($answers['phone'] ?? ''));
    $code = 'FIYFF-' . strtoupper(bin2hex(random_bytes(4)));
    $snapshot = array_map(static fn (array $field): array => [
        'key' => $field['field_key'],
        'label' => $field['label'],
        'section_key' => $field['section_key'],
        'section_title' => $field['section_title'],
        'type' => $field['field_type'],
    ], $fields);

    $storedPaths = [];
    db()->beginTransaction();
    try {
        $stmt = db()->prepare(
            'INSERT INTO grant_applications
             (event_id, form_id, applicant_code, full_name, email, phone, location, answers_json, form_snapshot_json, eligibility_complete, consented_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())'
        );
        $stmt->execute([
            $form['event_id'], $form['id'], $code, $fullName, $email, $phone, $location,
            json_encode($answers, JSON_THROW_ON_ERROR),
            json_encode($snapshot, JSON_THROW_ON_ERROR),
        ]);
        $applicationId = (int) db()->lastInsertId();
        foreach ($fileQueue as $queued) {
            $path = GrantDocumentUploader::store($queued['file'], $queued['validated']);
            $storedPaths[] = $path;
            db()->prepare(
                'INSERT INTO grant_application_documents
                 (application_id, field_id, field_key, original_name, storage_path, mime_type, size_bytes)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $applicationId,
                $queued['field']['id'],
                $queued['field']['field_key'],
                $queued['validated']['original_name'],
                $path,
                $queued['validated']['mime'],
                $queued['validated']['size'],
            ]);
        }
        db()->commit();
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        foreach ($storedPaths as $path) {
            $absolute = BASE_PATH . '/storage/' . $path;
            if (is_file($absolute)) {
                unlink($absolute);
            }
        }
        throw $exception;
    }

    send_managed_grant_confirmation($applicationId, $code, $fullName, $email, $form);
    clear_old();
    $_SESSION['grant_code'] = $code;
    flash('success', 'Your application has been submitted securely. Reference: ' . $code);
    redirect('/grants/' . $slug . '/apply?sent=1');
}

function grant_form_sections(array $fields): array
{
    $sections = [];
    foreach ($fields as $field) {
        $key = (string) $field['section_key'];
        if (!isset($sections[$key])) {
            $sections[$key] = [
                'key' => $key,
                'title' => $field['section_title'],
                'fields' => [],
            ];
        }
        $sections[$key]['fields'][] = $field;
    }
    return array_values($sections);
}

function grant_form_availability(array $form): array
{
    if ($form['status'] !== 'published') {
        return ['accepting' => false, 'message' => 'This grant application is currently closed.'];
    }
    $now = new DateTimeImmutable('now');
    if (!empty($form['opens_at']) && $now < new DateTimeImmutable($form['opens_at'])) {
        return ['accepting' => false, 'message' => 'Applications open on ' . format_date($form['opens_at'], 'F j, Y \a\t g:i A') . '.'];
    }
    if (!empty($form['closes_at']) && $now > new DateTimeImmutable($form['closes_at'])) {
        return ['accepting' => false, 'message' => 'Applications closed on ' . format_date($form['closes_at'], 'F j, Y \a\t g:i A') . '.'];
    }
    return ['accepting' => true, 'message' => 'Applications are open.'];
}

function send_managed_grant_confirmation(int $applicationId, string $code, string $name, string $email, array $form): void
{
    if (!Mailer::confirmationsEnabled()) {
        return;
    }
    Mailer::send(
        $email,
        $name,
        'Grant application received — ' . $code,
        '<h1 style="color:#6e3345;margin-top:0">Your application was received</h1><p>Hello ' . e($name) . ',</p><p>Your application for <strong>' . e($form['title']) . '</strong> has been submitted securely.</p><p>Your reference is <strong>' . e($code) . '</strong>. Please keep it for future communication.</p>',
        'grant_received',
        'grant_application',
        $applicationId
    );
    $recipient = trim((string) ($form['notification_email'] ?: setting('smtp_admin_email')));
    if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        Mailer::send(
            $recipient,
            'FIYFF Grant Team',
            'New grant application — ' . $code,
            '<h1 style="color:#6e3345;margin-top:0">New grant application</h1><p><strong>Applicant:</strong> ' . e($name) . '</p><p><strong>Grant:</strong> ' . e($form['title']) . '</p><p><strong>Reference:</strong> ' . e($code) . '</p><p>Sign in to the administration area to review the protected application and documents.</p>',
            'admin_grant_notification',
            'grant_application',
            $applicationId
        );
    }
}

function begin_appointment_payment(int $appointmentId): string
{
    $appointment = query_one('SELECT * FROM appointments WHERE id = ? LIMIT 1', [$appointmentId]);
    if (!$appointment) {
        throw new RuntimeException('The appointment could not be found.');
    }
    if ($appointment['payment_status'] === 'paid') {
        return url('/appointment/status/' . $appointment['booking_code']);
    }
    if (in_array($appointment['status'], ['cancelled', 'completed'], true)) {
        throw new RuntimeException('This appointment can no longer accept payment.');
    }
    if (!PaystackClient::configured() || (int) $appointment['amount_due'] < 1) {
        throw new RuntimeException('Online payment is not available.');
    }

    $reference = 'EMB-' . strtoupper(bin2hex(random_bytes(8)));
    $stmt = db()->prepare(
        "INSERT INTO appointment_payments (appointment_id, reference, amount, currency, status)
         VALUES (?, ?, ?, ?, 'initialized')"
    );
    $stmt->execute([$appointmentId, $reference, (int) $appointment['amount_due'], $appointment['currency']]);
    $paymentId = (int) db()->lastInsertId();

    try {
        $response = PaystackClient::fromSettings()->initializeTransaction([
            'email' => $appointment['email'],
            'amount' => (int) $appointment['amount_due'],
            'currency' => $appointment['currency'],
            'reference' => $reference,
            'callback_url' => url('/payments/paystack/callback'),
            'metadata' => [
                'appointment_id' => (int) $appointment['id'],
                'booking_code' => $appointment['booking_code'],
                'consultation_type' => $appointment['consultation_type'],
                'cancel_action' => url('/appointment/status/' . $appointment['booking_code']),
            ],
        ]);
        $data = $response['data'] ?? [];
        $authorizationUrl = trim((string) ($data['authorization_url'] ?? ''));
        $accessCode = trim((string) ($data['access_code'] ?? ''));
        if (!str_starts_with($authorizationUrl, 'https://checkout.paystack.com/')) {
            throw new RuntimeException('Paystack did not return a valid checkout link.');
        }
        db()->prepare(
            "UPDATE appointment_payments
             SET access_code = ?, authorization_url = ?, status = 'pending', raw_response_json = ?
             WHERE id = ?"
        )->execute([$accessCode, $authorizationUrl, json_encode($response, JSON_THROW_ON_ERROR), $paymentId]);
        db()->prepare(
            "UPDATE appointments SET payment_reference = ?, payment_status = 'pending', status = 'pending_payment' WHERE id = ?"
        )->execute([$reference, $appointmentId]);
        return $authorizationUrl;
    } catch (Throwable $exception) {
        db()->prepare(
            "UPDATE appointment_payments SET status = 'failed', gateway_response = ? WHERE id = ?"
        )->execute([mb_substr($exception->getMessage(), 0, 255), $paymentId]);
        throw $exception;
    }
}

function retry_appointment_payment(string $bookingCode): void
{
    verify_csrf();
    if (form_rate_limited('appointment_payment', 5)) {
        flash('error', 'Please wait a moment before starting checkout again.');
        redirect('/appointment/status/' . $bookingCode);
    }
    $appointment = query_one('SELECT * FROM appointments WHERE booking_code = ? LIMIT 1', [$bookingCode]);
    if (!$appointment) {
        public_not_found();
        return;
    }
    if ($appointment['payment_status'] === 'paid') {
        redirect('/appointment/status/' . $bookingCode);
    }
    if (in_array($appointment['status'], ['cancelled', 'completed'], true)) {
        flash('error', 'This appointment can no longer accept payment.');
        redirect('/appointment/status/' . $bookingCode);
    }
    try {
        redirect(begin_appointment_payment((int) $appointment['id']));
    } catch (Throwable $exception) {
        db()->prepare("UPDATE appointments SET payment_status = 'failed' WHERE id = ?")->execute([$appointment['id']]);
        flash('error', 'Secure checkout could not start. Please try again or contact us for help.');
        redirect('/appointment/status/' . $bookingCode);
    }
}

function handle_paystack_callback(): void
{
    $reference = trim((string) ($_GET['reference'] ?? ''));
    if ($reference === '' || mb_strlen($reference) > 100) {
        flash('error', 'The payment reference was missing.');
        redirect('/appointment');
    }
    $payment = query_one(
        'SELECT p.*, a.booking_code FROM appointment_payments p JOIN appointments a ON a.id = p.appointment_id WHERE p.reference = ? LIMIT 1',
        [$reference]
    );
    if (!$payment) {
        flash('error', 'This payment reference is not linked to an appointment.');
        redirect('/appointment');
    }
    try {
        $response = PaystackClient::fromSettings()->verifyTransaction($reference);
        $data = $response['data'] ?? [];
        if (($data['status'] ?? '') !== 'success') {
            $gatewayStatus = in_array(($data['status'] ?? ''), ['failed', 'abandoned', 'reversed'], true) ? $data['status'] : 'pending';
            db()->prepare('UPDATE appointment_payments SET status = ?, gateway_response = ?, raw_response_json = ? WHERE id = ?')
                ->execute([
                    $gatewayStatus,
                    mb_substr((string) ($data['gateway_response'] ?? 'Payment has not completed.'), 0, 255),
                    json_encode($response, JSON_THROW_ON_ERROR),
                    $payment['id'],
                ]);
            flash('error', 'Payment has not been completed. You can retry securely below.');
        } else {
            complete_paystack_payment($data, 'callback', $response);
            flash('success', 'Payment confirmed. Your appointment request is now ready for scheduling.');
        }
    } catch (Throwable $exception) {
        flash('error', 'We could not verify the payment yet. Please refresh this page in a moment.');
    }
    redirect('/appointment/status/' . $payment['booking_code']);
}

function handle_paystack_webhook(): void
{
    $payload = (string) file_get_contents('php://input');
    $signature = trim((string) ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? ''));
    if (!PaystackClient::validWebhookSignature($payload, $signature)) {
        http_response_code(401);
        echo 'Invalid signature';
        return;
    }
    $event = json_decode($payload, true);
    if (!is_array($event)) {
        http_response_code(400);
        echo 'Invalid payload';
        return;
    }
    if (($event['event'] ?? '') === 'charge.success' && is_array($event['data'] ?? null)) {
        try {
            complete_paystack_payment($event['data'], 'webhook', $event);
        } catch (Throwable) {
            http_response_code(422);
            echo 'Event not accepted';
            return;
        }
    }
    http_response_code(200);
    echo 'OK';
}

function complete_paystack_payment(array $data, string $source, array $raw): void
{
    $reference = trim((string) ($data['reference'] ?? ''));
    if ($reference === '' || ($data['status'] ?? '') !== 'success') {
        throw new RuntimeException('The transaction is not successful.');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT p.*, a.booking_code, a.email_confirmation_sent_at,
                    a.payment_status AS appointment_payment_status, a.payment_reference AS appointment_payment_reference
             FROM appointment_payments p
             JOIN appointments a ON a.id = p.appointment_id
             WHERE p.reference = ? FOR UPDATE'
        );
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();
        if (!$payment) {
            throw new RuntimeException('Unknown payment reference.');
        }
        if ((int) ($data['amount'] ?? -1) !== (int) $payment['amount']) {
            throw new RuntimeException('Payment amount mismatch.');
        }
        if (strtoupper((string) ($data['currency'] ?? '')) !== strtoupper((string) $payment['currency'])) {
            throw new RuntimeException('Payment currency mismatch.');
        }
        if ($payment['status'] === 'success') {
            $pdo->commit();
            return;
        }

        $rawResponse = $source === 'callback' ? json_encode($raw, JSON_THROW_ON_ERROR) : $payment['raw_response_json'];
        $rawWebhook = $source === 'webhook' ? json_encode($raw, JSON_THROW_ON_ERROR) : $payment['raw_webhook_json'];
        $pdo->prepare(
            "UPDATE appointment_payments
             SET status = 'success', gateway_response = ?, channel = ?, paid_at = NOW(),
                 raw_response_json = ?, raw_webhook_json = ?
             WHERE id = ?"
        )->execute([
            mb_substr((string) ($data['gateway_response'] ?? 'Successful'), 0, 255),
            mb_substr((string) ($data['channel'] ?? ''), 0, 60),
            $rawResponse,
            $rawWebhook,
            $payment['id'],
        ]);

        $shouldEmail = empty($payment['email_confirmation_sent_at']) && Mailer::confirmationsEnabled();
        $pdo->prepare(
            "UPDATE appointments
             SET payment_status = 'paid', status = IF(status = 'pending_payment', 'new', status),
                 payment_reference = IF(payment_status = 'paid' AND payment_reference IS NOT NULL, payment_reference, ?),
                 paid_at = IF(paid_at IS NULL, NOW(), paid_at),
                 email_confirmation_sent_at = IF(? = 1, NOW(), email_confirmation_sent_at)
             WHERE id = ?"
        )->execute([$reference, $shouldEmail ? 1 : 0, $payment['appointment_id']]);
        $pdo->commit();

        if ($shouldEmail && !send_appointment_confirmation((int) $payment['appointment_id'], true)) {
            db()->prepare('UPDATE appointments SET email_confirmation_sent_at = NULL WHERE id = ?')
                ->execute([$payment['appointment_id']]);
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function show_appointment_status(string $bookingCode): void
{
    $appointment = query_one('SELECT * FROM appointments WHERE booking_code = ? LIMIT 1', [$bookingCode]);
    if (!$appointment) {
        public_not_found();
        return;
    }
    $payments = query_all(
        'SELECT reference, amount, currency, status, gateway_response, paid_at, created_at
         FROM appointment_payments WHERE appointment_id = ? ORDER BY created_at DESC',
        [$appointment['id']]
    );
    render('public/appointment-status', [
        'appointment' => $appointment,
        'payments' => $payments,
        'title' => 'Appointment ' . $appointment['booking_code'],
        'description' => 'Review the status of your Emb Chronicles appointment request.',
    ]);
}

function send_appointment_confirmation(int $appointmentId, bool $paid): bool
{
    if (!Mailer::confirmationsEnabled()) {
        return false;
    }
    $appointment = query_one('SELECT * FROM appointments WHERE id = ? LIMIT 1', [$appointmentId]);
    if (!$appointment) {
        return false;
    }
    $paymentLine = $paid
        ? '<p><strong>Payment:</strong> ' . e(format_money((int) $appointment['amount_due'], $appointment['currency'])) . ' confirmed</p>'
        : '<p><strong>Payment:</strong> No online payment required</p>';
    $date = $appointment['preferred_date'] ? e(format_date($appointment['preferred_date'])) : 'Flexible';
    $time = $appointment['preferred_time'] ? e(substr($appointment['preferred_time'], 0, 5)) : 'Flexible';
    $html = '<h1 style="color:#6e3345;margin-top:0">Your appointment request is confirmed</h1>'
        . '<p>Hello ' . e($appointment['name']) . ',</p>'
        . '<p>We have received your request and will contact you to confirm the final schedule.</p>'
        . '<div style="background:#faf6f4;border-radius:12px;padding:18px">'
        . '<p><strong>Reference:</strong> ' . e($appointment['booking_code']) . '</p>'
        . '<p><strong>Session:</strong> ' . e($appointment['consultation_type']) . '</p>'
        . '<p><strong>Preferred timing:</strong> ' . $date . ' at ' . $time . '</p>'
        . $paymentLine . '</div>'
        . '<p>Please keep this reference for any follow-up.</p>';
    $sent = Mailer::send(
        $appointment['email'],
        $appointment['name'],
        $paid ? 'Payment confirmed — ' . $appointment['booking_code'] : 'Appointment request received — ' . $appointment['booking_code'],
        $html,
        $paid ? 'appointment_paid' : 'appointment_received',
        'appointment',
        $appointmentId
    );
    if ($sent) {
        db()->prepare('UPDATE appointments SET email_confirmation_sent_at = NOW() WHERE id = ?')->execute([$appointmentId]);
    }

    $admin = trim((string) setting('smtp_admin_email'));
    if ($sent && filter_var($admin, FILTER_VALIDATE_EMAIL)) {
        Mailer::send(
            $admin,
            'Emb Chronicles Admin',
            'New appointment — ' . $appointment['booking_code'],
            '<h1 style="color:#6e3345;margin-top:0">New appointment request</h1>'
            . '<p><strong>Client:</strong> ' . e($appointment['name']) . ' (' . e($appointment['email']) . ')</p>'
            . '<p><strong>Session:</strong> ' . e($appointment['consultation_type']) . '</p>'
            . '<p><strong>Payment:</strong> ' . e($appointment['payment_status']) . '</p>'
            . '<p><a href="' . e(url('/admin/appointments?view=' . $appointmentId)) . '">Open the appointment in admin</a></p>',
            'admin_new_appointment',
            'appointment',
            $appointmentId
        );
    }
    return $sent;
}

function send_contact_confirmation(int $contactId, array $data): void
{
    if (!Mailer::confirmationsEnabled()) {
        return;
    }
    Mailer::send(
        $data['email'],
        $data['name'],
        'We received your message',
        '<h1 style="color:#6e3345;margin-top:0">Thank you for reaching out</h1><p>Hello ' . e($data['name']) . ',</p><p>Your message has reached Emb Chronicles. We will respond using the contact details you provided.</p>',
        'contact_received',
        'contact_submission',
        $contactId
    );
    $admin = trim((string) setting('smtp_admin_email'));
    if (filter_var($admin, FILTER_VALIDATE_EMAIL)) {
        Mailer::send(
            $admin,
            'Emb Chronicles Admin',
            'New website enquiry from ' . $data['name'],
            '<h1 style="color:#6e3345;margin-top:0">New contact submission</h1><p><strong>From:</strong> ' . e($data['name']) . ' (' . e($data['email']) . ')</p><p><strong>Topic:</strong> ' . e($data['topic'] ?: 'General enquiry') . '</p><p>' . nl2br(e($data['message'])) . '</p>',
            'admin_contact_notification',
            'contact_submission',
            $contactId
        );
    }
}

function send_grant_confirmation(int $grantId, string $code, string $name, string $email, string $eventTitle): void
{
    if (!Mailer::confirmationsEnabled()) {
        return;
    }
    Mailer::send(
        $email,
        $name,
        'FIYFF application received — ' . $code,
        '<h1 style="color:#6e3345;margin-top:0">Your application was received</h1><p>Hello ' . e($name) . ',</p><p>Your application for <strong>' . e($eventTitle) . '</strong> has been submitted securely.</p><p>Your reference is <strong>' . e($code) . '</strong>. Please keep it for future communication.</p>',
        'grant_received',
        'grant_application',
        $grantId
    );
}

function public_sitemap(): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    $paths = ['', 'about', 'services', 'events', 'fiyff-foundation', 'community', 'contact', 'appointment', 'privacy'];
    foreach (query_all("SELECT slug FROM services WHERE status = 'published'") as $item) {
        $paths[] = 'services/' . $item['slug'];
    }
    foreach (query_all("SELECT slug FROM events WHERE status = 'published'") as $item) {
        $paths[] = 'events/' . $item['slug'];
    }
    foreach (query_all("SELECT slug FROM grant_forms WHERE status = 'published'") as $item) {
        $paths[] = 'grants/' . $item['slug'] . '/apply';
    }
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($paths as $path) {
        echo '  <url><loc>' . e(url($path)) . "</loc></url>\n";
    }
    echo "</urlset>";
}

function public_not_found(): void
{
    http_response_code(404);
    render('errors/404', ['title' => 'Page not found']);
}
