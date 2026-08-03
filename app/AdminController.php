<?php
declare(strict_types=1);

function admin_dispatch(string $path): void
{
    if ($path === '/admin/login') {
        admin_login();
        return;
    }
    if ($path === '/admin/logout') {
        if (is_post()) {
            verify_csrf();
            logout_admin();
        }
        redirect('/admin/login');
    }

    require_auth();
    $routePermissions = [
        '/admin' => 'dashboard.view',
        '/admin/dashboard' => 'dashboard.view',
        '/admin/events' => 'events.manage',
        '/admin/services' => 'services.manage',
        '/admin/hero-slides' => 'heroes.manage',
        '/admin/testimonials' => 'testimonials.manage',
        '/admin/settings' => 'settings.manage',
        '/admin/contact-submissions' => 'contacts.manage',
        '/admin/appointments' => 'appointments.manage',
        '/admin/appointment-types' => 'appointments.manage',
        '/admin/availability' => 'appointments.manage',
        '/admin/email-log' => 'email_log.view',
        '/admin/page-content' => 'content.manage',
        '/admin/grant-forms' => 'grants.manage',
        '/admin/grant-applications' => 'grants.manage',
        '/admin/users' => 'users.manage',
        '/admin/roles' => 'roles.manage',
    ];
    if (isset($routePermissions[$path])) {
        require_permission($routePermissions[$path]);
    }
    if ($path === '/admin/data-tools' && !can_any(['services.manage', 'events.manage', 'testimonials.manage', 'contacts.manage', 'grants.manage'])) {
        http_response_code(403);
        render('errors/403', ['title' => 'Access denied'], 'admin');
        return;
    }
    if (preg_match('#^/admin/grant-documents/(\d+)$#', $path, $matches)) {
        require_permission('grants.manage');
        admin_grant_document((int) $matches[1]);
        return;
    }

    switch ($path) {
        case '/admin':
        case '/admin/dashboard':
            admin_dashboard();
            return;
        case '/admin/events':
            admin_events();
            return;
        case '/admin/services':
            admin_services();
            return;
        case '/admin/hero-slides':
            admin_heroes();
            return;
        case '/admin/testimonials':
            admin_testimonials();
            return;
        case '/admin/settings':
            admin_settings();
            return;
        case '/admin/contact-submissions':
            admin_contacts();
            return;
        case '/admin/appointments':
            admin_appointments();
            return;
        case '/admin/appointment-types':
            admin_appointment_types();
            return;
        case '/admin/availability':
            admin_availability();
            return;
        case '/admin/data-tools':
            admin_data_tools();
            return;
        case '/admin/email-log':
            admin_email_log();
            return;
        case '/admin/page-content':
            admin_page_content();
            return;
        case '/admin/grant-applications':
            admin_grants();
            return;
        case '/admin/grant-forms':
            admin_grant_forms();
            return;
        case '/admin/users':
            admin_users();
            return;
        case '/admin/roles':
            admin_roles();
            return;
    }

    http_response_code(404);
    render('errors/404', ['title' => 'Admin page not found', 'adminError' => true], 'admin');
}

function admin_login(): void
{
    if (auth_user()) {
        redirect('/admin');
    }
    if (is_post()) {
        verify_csrf();
        if (form_rate_limited('admin_login', 2)) {
            flash('error', 'Please wait a moment before trying again.');
            redirect('/admin/login');
        }
        if (attempt_login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            flash('success', 'Welcome back.');
            $intended = $_SESSION['_intended'] ?? '/admin';
            unset($_SESSION['_intended']);
            redirect((string) $intended);
        }
        flash('error', 'The email or password is incorrect.');
        redirect('/admin/login');
    }
    render('admin/login', ['title' => 'Admin sign in'], 'auth');
}

function admin_dashboard(): void
{
    $metrics = [];
    if (can('services.manage')) {
        $metrics[] = ['Total services', (int) query_value('SELECT COUNT(*) FROM services'), '/admin/services', 'sage'];
    }
    if (can('events.manage')) {
        $metrics[] = ['Total events', (int) query_value('SELECT COUNT(*) FROM events'), '/admin/events', 'berry'];
        $metrics[] = ['Featured events', (int) query_value("SELECT COUNT(*) FROM events WHERE is_featured = 1 AND status = 'published'"), '/admin/events', 'amber'];
    }
    if (can('contacts.manage')) {
        $metrics[] = ['Unread contacts', (int) query_value('SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0 AND is_archived = 0'), '/admin/contact-submissions?filter=unread', 'berry'];
    }
    if (can('appointments.manage')) {
        $metrics[] = ['New appointments', (int) query_value("SELECT COUNT(*) FROM appointments WHERE status = 'new'"), '/admin/appointments', 'sage'];
        $metrics[] = ['Pending payments', (int) query_value("SELECT COUNT(*) FROM appointments WHERE payment_status = 'pending'"), '/admin/appointments?payment=pending', 'amber'];
    }
    if (can('grants.manage')) {
        $metrics[] = ['New grant applications', (int) query_value("SELECT COUNT(*) FROM grant_applications WHERE status = 'submitted'"), '/admin/grant-applications', 'berry'];
    }
    $upcoming = can('events.manage') ? query_all('SELECT * FROM events ORDER BY event_date ASC LIMIT 6') : [];
    $contacts = can('contacts.manage') ? query_all('SELECT * FROM contact_submissions WHERE is_archived = 0 ORDER BY created_at DESC LIMIT 6') : [];
    $activity = can_any(['users.manage', 'roles.manage'])
        ? query_all('SELECT a.*, admins.name AS admin_name FROM audit_log a LEFT JOIN admins ON admins.id = a.admin_id ORDER BY a.created_at DESC LIMIT 8')
        : [];
    $heroCount = can('heroes.manage') ? (int) query_value('SELECT COUNT(*) FROM hero_slides WHERE is_active = 1') : null;
    $showMedia = can_any(['events.manage', 'services.manage', 'heroes.manage', 'testimonials.manage', 'content.manage']);
    render('admin/dashboard', compact('metrics', 'upcoming', 'contacts', 'activity', 'heroCount', 'showMedia') + ['title' => 'Dashboard'], 'admin');
}

function admin_events(): void
{
    if (is_post()) {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? 'save');
        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
            audit('delete', 'event', $id);
            flash('success', 'Event deleted.');
            redirect('/admin/events');
        }
        save_event();
    }
    $search = trim((string) ($_GET['q'] ?? ''));
    $params = [];
    $where = '1=1';
    if ($search !== '') {
        $where = '(title LIKE ? OR event_type LIKE ? OR location LIKE ?)';
        $term = '%' . $search . '%';
        $params = [$term, $term, $term];
    }
    $events = query_all("SELECT * FROM events WHERE {$where} ORDER BY event_date DESC, created_at DESC", $params);
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM events WHERE id = ?', [(int) $_GET['edit']]) : null;
    $gallery = $editing ? query_all('SELECT * FROM event_media WHERE event_id = ? ORDER BY sort_order, id', [$editing['id']]) : [];
    render('admin/events', compact('events', 'editing', 'gallery', 'search') + ['title' => 'Events'], 'admin');
}

function save_event(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '' || trim((string) ($_POST['excerpt'] ?? '')) === '') {
        flash('error', 'Title and excerpt are required.');
        redirect('/admin/events' . ($id ? '?edit=' . $id : ''));
    }
    $existing = $id ? query_one('SELECT * FROM events WHERE id = ?', [$id]) : null;
    $cover = $existing['cover_image'] ?? '';
    $coverAlt = trim((string) ($_POST['cover_alt'] ?? ($existing['cover_alt'] ?? '')));
    if (!empty($_POST['remove_cover'])) {
        $cover = '';
        $coverAlt = '';
    } elseif (!empty($_FILES['cover_image']['name'])) {
        try {
            $media = MediaUploader::store($_FILES['cover_image'], $coverAlt);
            $cover = $media['path'];
            $coverAlt = $media['alt_text'];
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            redirect('/admin/events' . ($id ? '?edit=' . $id : ''));
        }
    } elseif (array_key_exists('cover_url', $_POST)) {
        $cover = trim((string) $_POST['cover_url']);
    }
    if ($cover !== '' && $coverAlt === '') {
        flash('error', 'Descriptive alt text is required when a cover image is used.');
        redirect('/admin/events' . ($id ? '?edit=' . $id : ''));
    }
    if ($cover === '') {
        $coverAlt = '';
    }
    $slug = slugify((string) ($_POST['slug'] ?: $title));
    $values = [
        $title, $slug, trim((string) $_POST['excerpt']), trim((string) ($_POST['description'] ?? '')),
        ($_POST['event_date'] ?? '') ?: null, ($_POST['event_end'] ?? '') ?: null,
        trim((string) ($_POST['timezone'] ?? 'Africa/Lagos')),
        trim((string) ($_POST['location_mode'] ?? 'physical')),
        trim((string) ($_POST['location'] ?? '')),
        trim((string) ($_POST['event_type'] ?? 'Community Event')),
        trim((string) ($_POST['external_link'] ?? '')), $cover, $coverAlt,
        isset($_POST['is_featured']) ? 1 : 0,
        ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
        trim((string) ($_POST['seo_title'] ?? '')),
        trim((string) ($_POST['seo_description'] ?? '')),
    ];
    if ($id) {
        $sql = 'UPDATE events SET title=?, slug=?, excerpt=?, description=?, event_date=?, event_end=?, timezone=?, location_mode=?, location=?, event_type=?, external_link=?, cover_image=?, cover_alt=?, is_featured=?, status=?, seo_title=?, seo_description=? WHERE id=?';
        $values[] = $id;
        db()->prepare($sql)->execute($values);
    } else {
        $sql = 'INSERT INTO events (title, slug, excerpt, description, event_date, event_end, timezone, location_mode, location, event_type, external_link, cover_image, cover_alt, is_featured, status, seo_title, seo_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        db()->prepare($sql)->execute($values);
        $id = (int) db()->lastInsertId();
    }
    remove_gallery_media('event', $id, $_POST['remove_gallery'] ?? []);
    save_gallery('event', $id, $_FILES['gallery'] ?? null, $_POST['gallery_alt'] ?? []);
    audit('save', 'event', $id);
    flash('success', 'Event saved successfully.');
    redirect('/admin/events?edit=' . $id);
}

function admin_services(): void
{
    if (is_post()) {
        verify_csrf();
        if (($_POST['action'] ?? 'save') === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
            audit('delete', 'service', $id);
            flash('success', 'Service deleted.');
            redirect('/admin/services');
        }
        save_service();
    }
    $services = query_all('SELECT * FROM services ORDER BY sort_order, created_at DESC');
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM services WHERE id = ?', [(int) $_GET['edit']]) : null;
    $gallery = $editing ? query_all('SELECT * FROM service_media WHERE service_id = ? ORDER BY sort_order, id', [$editing['id']]) : [];
    render('admin/services', compact('services', 'editing', 'gallery') + ['title' => 'Services'], 'admin');
}

function save_service(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    $excerptText = trim((string) ($_POST['excerpt'] ?? ''));
    if ($title === '' || $excerptText === '') {
        flash('error', 'Title and excerpt are required.');
        redirect('/admin/services' . ($id ? '?edit=' . $id : ''));
    }
    $existing = $id ? query_one('SELECT * FROM services WHERE id = ?', [$id]) : null;
    $cover = $existing['cover_image'] ?? '';
    $coverAlt = trim((string) ($_POST['cover_alt'] ?? ($existing['cover_alt'] ?? '')));
    if (!empty($_POST['remove_cover'])) {
        $cover = '';
        $coverAlt = '';
    } elseif (!empty($_FILES['cover_image']['name'])) {
        try {
            $media = MediaUploader::store($_FILES['cover_image'], $coverAlt);
            $cover = $media['path'];
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            redirect('/admin/services' . ($id ? '?edit=' . $id : ''));
        }
    } elseif (array_key_exists('cover_url', $_POST)) {
        $cover = trim((string) $_POST['cover_url']);
    }
    if ($cover !== '' && $coverAlt === '') {
        flash('error', 'Alt text is required when a cover image is used.');
        redirect('/admin/services' . ($id ? '?edit=' . $id : ''));
    }
    if ($cover === '') {
        $coverAlt = '';
    }
    $values = [
        $title, slugify((string) ($_POST['slug'] ?: $title)), $excerptText,
        trim((string) ($_POST['description'] ?? '')), $cover, $coverAlt,
        (int) ($_POST['sort_order'] ?? 0), isset($_POST['is_pinned']) ? 1 : 0,
        ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
        trim((string) ($_POST['seo_title'] ?? '')), trim((string) ($_POST['seo_description'] ?? '')),
    ];
    if ($id) {
        $values[] = $id;
        db()->prepare('UPDATE services SET title=?, slug=?, excerpt=?, description=?, cover_image=?, cover_alt=?, sort_order=?, is_pinned=?, status=?, seo_title=?, seo_description=? WHERE id=?')->execute($values);
    } else {
        db()->prepare('INSERT INTO services (title, slug, excerpt, description, cover_image, cover_alt, sort_order, is_pinned, status, seo_title, seo_description) VALUES (?,?,?,?,?,?,?,?,?,?,?)')->execute($values);
        $id = (int) db()->lastInsertId();
    }
    remove_gallery_media('service', $id, $_POST['remove_gallery'] ?? []);
    save_gallery('service', $id, $_FILES['gallery'] ?? null, $_POST['gallery_alt'] ?? []);
    audit('save', 'service', $id);
    flash('success', 'Service saved successfully.');
    redirect('/admin/services?edit=' . $id);
}

function save_gallery(string $type, int $parentId, ?array $files, array|string $alts): void
{
    if (!$files || empty($files['name']) || !is_array($files['name'])) {
        return;
    }
    $table = $type === 'event' ? 'event_media' : 'service_media';
    $foreign = $type === 'event' ? 'event_id' : 'service_id';
    foreach ($files['name'] as $index => $name) {
        if ($name === '') {
            continue;
        }
        $file = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
        $alt = is_array($alts) ? trim((string) ($alts[$index] ?? '')) : trim((string) $alts);
        if ($alt === '') {
            continue;
        }
        try {
            $media = MediaUploader::store($file, $alt);
            $stmt = db()->prepare("INSERT INTO {$table} ({$foreign}, image_path, alt_text, responsive_json, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$parentId, $media['path'], $alt, json_encode($media['variants']), $index]);
        } catch (RuntimeException $exception) {
            flash('error', 'Gallery image skipped: ' . $exception->getMessage());
        }
    }
}

function remove_gallery_media(string $type, int $parentId, mixed $mediaIds): void
{
    if (!is_array($mediaIds)) {
        return;
    }
    $table = $type === 'event' ? 'event_media' : 'service_media';
    $foreign = $type === 'event' ? 'event_id' : 'service_id';
    $stmt = db()->prepare("DELETE FROM {$table} WHERE id = ? AND {$foreign} = ?");
    foreach (array_unique(array_map('intval', $mediaIds)) as $mediaId) {
        if ($mediaId > 0) {
            $stmt->execute([$mediaId, $parentId]);
        }
    }
}

function admin_heroes(): void
{
    if (is_post()) {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (int) ($_POST['id'] ?? 0);
        if ($action === 'delete') {
            db()->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$id]);
            audit('delete', 'hero_slide', $id);
            flash('success', 'Hero slide deleted.');
            redirect('/admin/hero-slides');
        }
        $existing = $id ? query_one('SELECT * FROM hero_slides WHERE id = ?', [$id]) : null;
        $image = $existing['image_path'] ?? trim((string) ($_POST['image_url'] ?? ''));
        $alt = trim((string) ($_POST['image_alt'] ?? ($existing['image_alt'] ?? '')));
        if (!empty($_POST['remove_image'])) {
            $image = '';
            $alt = '';
        } elseif (!empty($_FILES['image']['name'])) {
            try {
                $media = MediaUploader::store($_FILES['image'], $alt);
                $image = $media['path'];
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/admin/hero-slides' . ($id ? '?edit=' . $id : ''));
            }
        } elseif (array_key_exists('image_url', $_POST)) {
            $image = trim((string) $_POST['image_url']);
        }
        if (trim((string) ($_POST['headline'] ?? '')) === '' || ($image !== '' && $alt === '')) {
            flash('error', 'A headline is required, and images need descriptive alt text.');
            redirect('/admin/hero-slides' . ($id ? '?edit=' . $id : ''));
        }
        $isActive = isset($_POST['is_active']) && $image !== '' ? 1 : 0;
        $values = [
            $image, $alt, trim((string) $_POST['headline']), trim((string) ($_POST['subheading'] ?? '')),
            trim((string) ($_POST['cta_label'] ?? '')), trim((string) ($_POST['cta_link'] ?? '')),
            trim((string) ($_POST['secondary_label'] ?? '')), trim((string) ($_POST['secondary_link'] ?? '')),
            (int) ($_POST['sort_order'] ?? 0), $isActive,
        ];
        if ($id) {
            $values[] = $id;
            db()->prepare('UPDATE hero_slides SET image_path=?, image_alt=?, headline=?, subheading=?, cta_label=?, cta_link=?, secondary_label=?, secondary_link=?, sort_order=?, is_active=? WHERE id=?')->execute($values);
        } else {
            db()->prepare('INSERT INTO hero_slides (image_path, image_alt, headline, subheading, cta_label, cta_link, secondary_label, secondary_link, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute($values);
            $id = (int) db()->lastInsertId();
        }
        audit('save', 'hero_slide', $id);
        flash('success', 'Hero slide saved.');
        redirect('/admin/hero-slides?edit=' . $id);
    }
    $slides = query_all('SELECT * FROM hero_slides ORDER BY sort_order, id');
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM hero_slides WHERE id = ?', [(int) $_GET['edit']]) : null;
    render('admin/heroes', compact('slides', 'editing') + ['title' => 'Hero slides'], 'admin');
}

function admin_testimonials(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        if (($_POST['action'] ?? 'save') === 'delete') {
            db()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
            audit('delete', 'testimonial', $id);
            flash('success', 'Testimonial deleted.');
            redirect('/admin/testimonials');
        }
        $existing = $id ? query_one('SELECT * FROM testimonials WHERE id = ?', [$id]) : null;
        $photo = $existing['photo_path'] ?? trim((string) ($_POST['photo_url'] ?? ''));
        $alt = trim((string) ($_POST['photo_alt'] ?? ($existing['photo_alt'] ?? '')));
        if (!empty($_POST['remove_photo'])) {
            $photo = '';
            $alt = '';
        } elseif (!empty($_FILES['photo']['name'])) {
            try {
                $media = MediaUploader::store($_FILES['photo'], $alt);
                $photo = $media['path'];
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/admin/testimonials' . ($id ? '?edit=' . $id : ''));
            }
        } elseif (array_key_exists('photo_url', $_POST)) {
            $photo = trim((string) $_POST['photo_url']);
        }
        if ($photo !== '' && $alt === '') {
            flash('error', 'Photo alt text is required when a testimonial photo is used.');
            redirect('/admin/testimonials' . ($id ? '?edit=' . $id : ''));
        }
        if ($photo === '') {
            $alt = '';
        }
        $values = [
            trim((string) ($_POST['client_name'] ?? '')), $photo, $alt,
            trim((string) ($_POST['quote'] ?? '')), (int) ($_POST['sort_order'] ?? 0),
            isset($_POST['is_visible']) ? 1 : 0,
        ];
        if ($values[0] === '' || $values[3] === '') {
            flash('error', 'Client name and quote are required.');
            redirect('/admin/testimonials' . ($id ? '?edit=' . $id : ''));
        }
        if ($id) {
            $values[] = $id;
            db()->prepare('UPDATE testimonials SET client_name=?, photo_path=?, photo_alt=?, quote=?, sort_order=?, is_visible=? WHERE id=?')->execute($values);
        } else {
            db()->prepare('INSERT INTO testimonials (client_name, photo_path, photo_alt, quote, sort_order, is_visible) VALUES (?,?,?,?,?,?)')->execute($values);
            $id = (int) db()->lastInsertId();
        }
        audit('save', 'testimonial', $id);
        flash('success', 'Testimonial saved.');
        redirect('/admin/testimonials?edit=' . $id);
    }
    $testimonials = query_all('SELECT * FROM testimonials ORDER BY sort_order, id');
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM testimonials WHERE id = ?', [(int) $_GET['edit']]) : null;
    render('admin/testimonials', compact('testimonials', 'editing') + ['title' => 'Testimonials'], 'admin');
}

function admin_settings(): void
{
    $allowed = [
        'site_name', 'tagline', 'phone', 'whatsapp', 'email', 'address', 'opening_hours',
        'instagram', 'tiktok', 'footer_blurb', 'stats_members', 'stats_families',
        'logo_path', 'default_meta_title', 'default_meta_description',
        'social_share_image', 'social_share_image_alt',
        'maintenance_title', 'maintenance_message', 'maintenance_end_at',
        'deployment_status_message', 'deployment_status_url',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_from_email',
        'smtp_from_name', 'smtp_reply_to', 'smtp_admin_email',
        'paystack_public_key', 'paystack_currency',
    ];
    $booleans = ['smtp_enabled', 'email_confirmations_enabled', 'paystack_enabled', 'maintenance_enabled'];
    $secrets = ['smtp_password', 'paystack_secret_key'];
    if (is_post()) {
        verify_csrf();
        if (!empty($_POST['remove_logo'])) {
            $_POST['logo_path'] = '';
        } elseif (!empty($_FILES['logo']['name'])) {
            try {
                $media = MediaUploader::store($_FILES['logo'], (string) ($_POST['site_name'] ?? 'Emb Chronicles') . ' logo');
                // Logos should retain their original alpha channel and sharp
                // edges rather than depending on a generated photo variant.
                $_POST['logo_path'] = $media['variants']['original'] ?? $media['path'];
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/admin/settings');
            }
        }
        if (!empty($_POST['remove_social_share_image'])) {
            $_POST['social_share_image'] = '';
            $_POST['social_share_image_alt'] = '';
        } elseif (!empty($_FILES['social_share_image_file']['name'])) {
            $socialImageAlt = trim((string) ($_POST['social_share_image_alt'] ?? ''));
            if ($socialImageAlt === '') {
                flash('error', 'Add descriptive alt text before uploading the social sharing image.');
                redirect('/admin/settings');
            }
            try {
                $media = MediaUploader::store($_FILES['social_share_image_file'], $socialImageAlt);
                $_POST['social_share_image'] = $media['variants']['original'] ?? $media['path'];
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/admin/settings');
            }
        }
        $socialImage = trim((string) ($_POST['social_share_image'] ?? ''));
        $socialImageAlt = trim((string) ($_POST['social_share_image_alt'] ?? ''));
        if ($socialImage !== '' && $socialImageAlt === '') {
            flash('error', 'Add descriptive alt text when a social sharing image is used.');
            redirect('/admin/settings');
        }
        if ($socialImage === '') {
            $_POST['social_share_image_alt'] = '';
        }
        foreach (['smtp_from_email', 'smtp_reply_to', 'smtp_admin_email'] as $emailKey) {
            $value = trim((string) ($_POST[$emailKey] ?? ''));
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Enter a valid email address for each SMTP sender and notification field.');
                redirect('/admin/settings');
            }
        }
        $statusUrl = trim((string) ($_POST['deployment_status_url'] ?? ''));
        if ($statusUrl !== '' && !filter_var($statusUrl, FILTER_VALIDATE_URL)) {
            flash('error', 'Enter a valid deployment status URL or leave it blank.');
            redirect('/admin/settings');
        }
        $maintenanceEnd = trim((string) ($_POST['maintenance_end_at'] ?? ''));
        if ($maintenanceEnd !== '') {
            try {
                $_POST['maintenance_end_at'] = (new DateTimeImmutable($maintenanceEnd))->format('Y-m-d H:i:s');
            } catch (Throwable) {
                flash('error', 'Enter a valid expected return date and time.');
                redirect('/admin/settings');
            }
        }
        $port = (int) ($_POST['smtp_port'] ?? 587);
        if ($port < 1 || $port > 65535) {
            flash('error', 'SMTP port must be between 1 and 65535.');
            redirect('/admin/settings');
        }
        $_POST['smtp_encryption'] = in_array(($_POST['smtp_encryption'] ?? ''), ['tls', 'ssl', 'none'], true)
            ? $_POST['smtp_encryption']
            : 'tls';
        $_POST['paystack_currency'] = 'NGN';
        $currentSettings = array_column(query_all('SELECT `key`, `value` FROM site_settings'), 'value', 'key');
        $effectivePaystackSecret = !empty($_POST['clear_paystack_secret_key'])
            ? ''
            : ((string) ($_POST['paystack_secret_key'] ?? '') !== ''
                ? (string) $_POST['paystack_secret_key']
                : (string) ($currentSettings['paystack_secret_key'] ?? ''));
        $paidAppointmentTypes = (int) query_value('SELECT COUNT(*) FROM appointment_types WHERE is_active = 1 AND price > 0');
        if (isset($_POST['paystack_enabled']) && ($effectivePaystackSecret === '' || $paidAppointmentTypes < 1)) {
            flash('error', 'Add a Paystack secret key and at least one active appointment type with a price before enabling payments.');
            redirect('/admin/settings');
        }
        if (isset($_POST['smtp_enabled'])
            && (trim((string) ($_POST['smtp_host'] ?? '')) === ''
                || !filter_var((string) ($_POST['smtp_from_email'] ?? ''), FILTER_VALIDATE_EMAIL))) {
            flash('error', 'Add an SMTP host and a valid from address before enabling email delivery.');
            redirect('/admin/settings');
        }

        $stmt = db()->prepare(
            'INSERT INTO site_settings (`key`, `value`, `type`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`)'
        );
        foreach ($allowed as $key) {
            if (array_key_exists($key, $_POST)) {
                $type = match (true) {
                    in_array($key, ['stats_members', 'stats_families', 'smtp_port'], true) => 'number',
                    in_array($key, ['smtp_from_email', 'smtp_reply_to', 'smtp_admin_email'], true) => 'email',
                    in_array($key, ['logo_path', 'social_share_image'], true) => 'image',
                    in_array($key, ['default_meta_description', 'footer_blurb', 'maintenance_message'], true) => 'textarea',
                    $key === 'maintenance_end_at' => 'datetime',
                    $key === 'deployment_status_url' => 'url',
                    default => 'text',
                };
                $stmt->execute([$key, trim((string) $_POST[$key]), $type]);
            }
        }
        foreach ($booleans as $key) {
            $stmt->execute([$key, isset($_POST[$key]) ? '1' : '0', 'boolean']);
        }
        foreach ($secrets as $key) {
            if (!empty($_POST['clear_' . $key])) {
                $stmt->execute([$key, '', 'secret']);
            } elseif (array_key_exists($key, $_POST) && (string) $_POST[$key] !== '') {
                $stmt->execute([$key, (string) $_POST[$key], 'secret']);
            }
        }
        audit('save', 'site_settings', null);
        if (($_POST['action'] ?? '') === 'test_email') {
            $recipient = trim((string) ($_POST['smtp_admin_email'] ?? ''));
            $sent = Mailer::send(
                $recipient,
                'Emb Chronicles Admin',
                'EMB Chronicles SMTP test',
                '<h1 style="color:#6e3345;margin-top:0">SMTP is working</h1><p>This test confirms that the website can send email using the SMTP settings saved in the admin dashboard.</p>',
                'smtp_test'
            );
            flash($sent ? 'success' : 'error', $sent ? 'Settings saved and the test email was sent.' : 'Settings were saved, but the test email could not be sent. Check the SMTP details and email log.');
        } else {
            flash('success', 'Site, email, and payment settings saved.');
        }
        redirect('/admin/settings');
    }
    $settings = query_all('SELECT * FROM site_settings ORDER BY `key`');
    $settings = array_column($settings, 'value', 'key');
    render('admin/settings', compact('settings') + ['title' => 'Site settings'], 'admin');
}

function admin_contacts(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $action = (string) ($_POST['action'] ?? 'read');
        if ($action === 'delete') {
            db()->prepare('DELETE FROM contact_submissions WHERE id = ?')->execute([$id]);
        } elseif ($action === 'archive') {
            db()->prepare('UPDATE contact_submissions SET is_archived = 1 WHERE id = ?')->execute([$id]);
        } elseif ($action === 'unread') {
            db()->prepare('UPDATE contact_submissions SET is_read = 0 WHERE id = ?')->execute([$id]);
        } else {
            db()->prepare('UPDATE contact_submissions SET is_read = 1 WHERE id = ?')->execute([$id]);
        }
        audit($action, 'contact_submission', $id);
        flash('success', 'Submission updated.');
        redirect('/admin/contact-submissions' . ($action !== 'delete' ? '?view=' . $id : ''));
    }
    $filter = (string) ($_GET['filter'] ?? 'all');
    $where = $filter === 'unread' ? 'is_read = 0 AND is_archived = 0' : ($filter === 'archived' ? 'is_archived = 1' : 'is_archived = 0');
    $contacts = query_all("SELECT * FROM contact_submissions WHERE {$where} ORDER BY created_at DESC");
    $selected = !empty($_GET['view']) ? query_one('SELECT * FROM contact_submissions WHERE id = ?', [(int) $_GET['view']]) : ($contacts[0] ?? null);
    if ($selected && !$selected['is_read']) {
        db()->prepare('UPDATE contact_submissions SET is_read = 1 WHERE id = ?')->execute([$selected['id']]);
    }
    render('admin/contacts', compact('contacts', 'selected', 'filter') + ['title' => 'Contact submissions'], 'admin');
}

function admin_appointments(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'new');
        $allowed = ['pending_payment', 'new', 'contacted', 'scheduled', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $status = 'new';
        }
        $scheduledAt = null;
        $scheduledInput = trim((string) ($_POST['scheduled_at'] ?? ''));
        if ($scheduledInput !== '') {
            try {
                $scheduledAt = (new DateTimeImmutable($scheduledInput))->format('Y-m-d H:i:s');
            } catch (Throwable) {
                flash('error', 'Enter a valid confirmed appointment date and time.');
                redirect('/admin/appointments?view=' . $id);
            }
        }
        db()->prepare('UPDATE appointments SET status = ?, scheduled_at = ?, admin_notes = ? WHERE id = ?')->execute([
            $status,
            $scheduledAt,
            trim((string) ($_POST['admin_notes'] ?? '')),
            $id,
        ]);
        if (!empty($_POST['send_confirmation'])) {
            $sent = send_appointment_admin_update($id);
            flash($sent ? 'success' : 'error', $sent ? 'Appointment updated and the client was emailed.' : 'Appointment updated, but the email could not be sent.');
        } else {
            flash('success', 'Appointment updated.');
        }
        audit('status:' . $status, 'appointment', $id);
        redirect('/admin/appointments?view=' . $id);
    }
    $paymentFilter = (string) ($_GET['payment'] ?? 'all');
    $allowedFilters = ['all', 'paid', 'pending', 'failed', 'not_required'];
    if (!in_array($paymentFilter, $allowedFilters, true)) {
        $paymentFilter = 'all';
    }
    $appointments = $paymentFilter === 'all'
        ? query_all('SELECT * FROM appointments ORDER BY created_at DESC')
        : query_all('SELECT * FROM appointments WHERE payment_status = ? ORDER BY created_at DESC', [$paymentFilter]);
    $selected = !empty($_GET['view']) ? query_one('SELECT * FROM appointments WHERE id = ?', [(int) $_GET['view']]) : ($appointments[0] ?? null);
    $payments = $selected
        ? query_all('SELECT * FROM appointment_payments WHERE appointment_id = ? ORDER BY created_at DESC', [$selected['id']])
        : [];
    render('admin/appointments', compact('appointments', 'selected', 'payments', 'paymentFilter') + ['title' => 'Appointments'], 'admin');
}

function admin_appointment_types(): void
{
    if (is_post()) {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (int) ($_POST['id'] ?? 0);
        if ($action === 'delete') {
            db()->prepare('DELETE FROM appointment_types WHERE id = ?')->execute([$id]);
            audit('delete', 'appointment_type', $id);
            flash('success', 'Appointment type removed. Existing booking records were preserved.');
            redirect('/admin/appointment-types');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priceInput = str_replace([',', ' '], '', trim((string) ($_POST['price'] ?? '0')));
        $sortOrder = max(0, min(10000, (int) ($_POST['sort_order'] ?? 0)));
        if ($name === '' || mb_strlen($name) > 190 || !preg_match('/^\d{1,9}(?:\.\d{1,2})?$/', $priceInput)) {
            flash('error', 'Enter a name and a valid non-negative price with no more than nine digits and two decimal places.');
            redirect('/admin/appointment-types' . ($id ? '?edit=' . $id : '?new=1'));
        }
        $price = money_to_subunit($priceInput);
        try {
            if ($id) {
                db()->prepare('UPDATE appointment_types SET name = ?, description = ?, price = ?, currency = ?, sort_order = ?, is_active = ? WHERE id = ?')
                    ->execute([$name, $description, $price, 'NGN', $sortOrder, isset($_POST['is_active']) ? 1 : 0, $id]);
            } else {
                db()->prepare('INSERT INTO appointment_types (name, description, price, currency, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$name, $description, $price, 'NGN', $sortOrder, isset($_POST['is_active']) ? 1 : 0]);
                $id = (int) db()->lastInsertId();
            }
        } catch (PDOException) {
            flash('error', 'An appointment type with that name already exists.');
            redirect('/admin/appointment-types' . ($id ? '?edit=' . $id : '?new=1'));
        }
        audit('save', 'appointment_type', $id);
        flash('success', 'Appointment type and price saved.');
        redirect('/admin/appointment-types?edit=' . $id);
    }

    $appointmentTypes = query_all('SELECT * FROM appointment_types ORDER BY sort_order, name');
    $editing = !empty($_GET['new'])
        ? null
        : (!empty($_GET['edit']) ? query_one('SELECT * FROM appointment_types WHERE id = ?', [(int) $_GET['edit']]) : ($appointmentTypes[0] ?? null));
    render('admin/appointment-types', compact('appointmentTypes', 'editing') + ['title' => 'Appointment types'], 'admin');
}

function admin_availability(): void
{
    if (is_post()) {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? 'save_settings');
        if ($action === 'save_settings') {
            $windowDays = max(1, min(365, (int) ($_POST['appointment_booking_window_days'] ?? 60)));
            $noticeHours = max(0, min(720, (int) ($_POST['appointment_min_notice_hours'] ?? 24)));
            $dailyLimit = max(1, min(100, (int) ($_POST['appointment_daily_limit'] ?? 6)));
            $stmt = db()->prepare(
                'INSERT INTO site_settings (`key`, `value`, `type`) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `type`=VALUES(`type`)'
            );
            foreach ([
                ['appointment_booking_enabled', isset($_POST['appointment_booking_enabled']) ? '1' : '0', 'boolean'],
                ['appointment_booking_window_days', (string) $windowDays, 'number'],
                ['appointment_min_notice_hours', (string) $noticeHours, 'number'],
                ['appointment_daily_limit', (string) $dailyLimit, 'number'],
            ] as $setting) {
                $stmt->execute($setting);
            }
            audit('save_settings', 'appointment_availability', null);
            flash('success', 'Booking availability settings saved.');
        } elseif ($action === 'save_slot') {
            $id = (int) ($_POST['id'] ?? 0);
            $weekday = (int) ($_POST['weekday'] ?? 0);
            $startTime = trim((string) ($_POST['start_time'] ?? ''));
            $duration = (int) ($_POST['duration_minutes'] ?? 60);
            $capacity = (int) ($_POST['capacity'] ?? 1);
            if ($weekday < 1 || $weekday > 7 || !preg_match('/^\d{2}:\d{2}$/', $startTime) || $duration < 15 || $duration > 480 || $capacity < 1 || $capacity > 50) {
                flash('error', 'Choose a valid weekday, start time, duration, and booking limit.');
                redirect('/admin/availability');
            }
            try {
                if ($id) {
                    db()->prepare('UPDATE appointment_availability_slots SET weekday=?, start_time=?, duration_minutes=?, capacity=?, is_active=? WHERE id=?')
                        ->execute([$weekday, $startTime, $duration, $capacity, isset($_POST['is_active']) ? 1 : 0, $id]);
                } else {
                    db()->prepare(
                        'INSERT INTO appointment_availability_slots (weekday, start_time, duration_minutes, capacity, is_active)
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE duration_minutes=VALUES(duration_minutes), capacity=VALUES(capacity), is_active=VALUES(is_active)'
                    )->execute([$weekday, $startTime, $duration, $capacity, isset($_POST['is_active']) ? 1 : 0]);
                    $id = (int) db()->lastInsertId();
                }
            } catch (PDOException) {
                flash('error', 'That weekday and start time already exist. Edit the existing slot instead.');
                redirect('/admin/availability');
            }
            audit('save_slot', 'appointment_availability', $id ?: null);
            flash('success', 'Time slot saved.');
        } elseif ($action === 'delete_slot') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM appointment_availability_slots WHERE id = ?')->execute([$id]);
            audit('delete_slot', 'appointment_availability', $id);
            flash('success', 'Time slot removed. Existing appointments were preserved.');
        } elseif ($action === 'block_date') {
            $date = trim((string) ($_POST['blocked_date'] ?? ''));
            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date || $parsed < new DateTimeImmutable('today')) {
                flash('error', 'Choose a valid date to block.');
                redirect('/admin/availability');
            }
            db()->prepare(
                'INSERT INTO appointment_blocked_dates (blocked_date, reason) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE reason=VALUES(reason)'
            )->execute([$date, trim((string) ($_POST['reason'] ?? ''))]);
            audit('block_date', 'appointment_availability', null);
            flash('success', 'Date blocked from new bookings.');
        } elseif ($action === 'unblock_date') {
            $id = (int) ($_POST['id'] ?? 0);
            db()->prepare('DELETE FROM appointment_blocked_dates WHERE id = ?')->execute([$id]);
            audit('unblock_date', 'appointment_availability', $id);
            flash('success', 'Date reopened for bookings.');
        }
        redirect('/admin/availability');
    }

    $slots = query_all('SELECT * FROM appointment_availability_slots ORDER BY weekday, start_time');
    $blockedDates = query_all('SELECT * FROM appointment_blocked_dates WHERE blocked_date >= CURRENT_DATE ORDER BY blocked_date');
    $availabilitySettings = [
        'enabled' => (string) setting('appointment_booking_enabled', '1') === '1',
        'window_days' => (int) setting('appointment_booking_window_days', 60),
        'notice_hours' => (int) setting('appointment_min_notice_hours', 24),
        'daily_limit' => (int) setting('appointment_daily_limit', 6),
    ];
    render('admin/availability', compact('slots', 'blockedDates', 'availabilitySettings') + ['title' => 'Booking availability'], 'admin');
}

function admin_data_tools(): void
{
    if (($_GET['action'] ?? '') === 'export') {
        try {
            $type = (string) ($_GET['type'] ?? '');
            data_transfer_definition($type);
            audit('export:' . $type, 'data_transfer', null);
            export_data_csv($type);
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            redirect('/admin/data-tools');
        }
    }
    if (is_post()) {
        verify_csrf();
        $type = (string) ($_POST['type'] ?? '');
        try {
            $count = import_data_csv($type, $_FILES['csv_file'] ?? null);
            audit('import:' . $type . ':' . $count, 'data_transfer', null);
            flash('success', $count . ' row' . ($count === 1 ? '' : 's') . ' imported successfully.');
        } catch (Throwable $exception) {
            flash('error', 'Import failed: ' . $exception->getMessage());
        }
        redirect('/admin/data-tools');
    }
    $datasets = array_filter(
        data_transfer_catalog(),
        static fn (array $definition): bool => can($definition['permission'])
    );
    render('admin/data-tools', compact('datasets') + ['title' => 'Import and export'], 'admin');
}

function send_appointment_admin_update(int $appointmentId): bool
{
    if (!Mailer::confirmationsEnabled()) {
        return false;
    }
    $appointment = query_one('SELECT * FROM appointments WHERE id = ? LIMIT 1', [$appointmentId]);
    if (!$appointment) {
        return false;
    }
    $schedule = $appointment['scheduled_at']
        ? '<p><strong>Confirmed schedule:</strong> ' . e(format_date($appointment['scheduled_at'], 'M j, Y · g:i A')) . '</p>'
        : '<p>The team will contact you when the final schedule is ready.</p>';
    return Mailer::send(
        $appointment['email'],
        $appointment['name'],
        'Appointment update — ' . $appointment['booking_code'],
        '<h1 style="color:#6e3345;margin-top:0">Your appointment has been updated</h1>'
        . '<p>Hello ' . e($appointment['name']) . ',</p>'
        . '<p><strong>Status:</strong> ' . e(ucwords(str_replace('_', ' ', $appointment['status']))) . '</p>'
        . $schedule
        . '<p><strong>Reference:</strong> ' . e($appointment['booking_code']) . '</p>',
        'appointment_update',
        'appointment',
        $appointmentId
    );
}

function admin_email_log(): void
{
    $status = (string) ($_GET['status'] ?? 'all');
    if (!in_array($status, ['all', 'sent', 'failed', 'skipped'], true)) {
        $status = 'all';
    }
    $messages = $status === 'all'
        ? query_all('SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 200')
        : query_all('SELECT * FROM email_logs WHERE status = ? ORDER BY created_at DESC LIMIT 200', [$status]);
    render('admin/email-log', compact('messages', 'status') + ['title' => 'Email log'], 'admin');
}

function admin_page_content(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $existing = $id ? query_one('SELECT * FROM page_content WHERE id = ?', [$id]) : null;
        $imagePath = trim((string) ($_POST['image_path'] ?? ($existing['image_path'] ?? '')));
        $imageAlt = trim((string) ($_POST['image_alt'] ?? ($existing['image_alt'] ?? '')));
        if (!empty($_POST['remove_image'])) {
            $imagePath = '';
            $imageAlt = '';
        } elseif (!empty($_FILES['supporting_image']['name'])) {
            if ($imageAlt === '') {
                flash('error', 'Add descriptive alt text before uploading a supporting image.');
                redirect('/admin/page-content' . ($id ? '?edit=' . $id : ''));
            }
            try {
                $media = MediaUploader::store($_FILES['supporting_image'], $imageAlt);
                $imagePath = $media['path'];
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/admin/page-content' . ($id ? '?edit=' . $id : ''));
            }
        }
        if ($imagePath !== '' && $imageAlt === '') {
            flash('error', 'Image alt text is required when a supporting image is used.');
            redirect('/admin/page-content' . ($id ? '?edit=' . $id : ''));
        }
        $values = [
            trim((string) ($_POST['page_key'] ?? '')),
            trim((string) ($_POST['section_key'] ?? '')),
            trim((string) ($_POST['eyebrow'] ?? '')),
            trim((string) ($_POST['heading'] ?? '')),
            trim((string) ($_POST['content'] ?? '')),
            $imagePath,
            $imageAlt,
            trim((string) ($_POST['link_label'] ?? '')),
            trim((string) ($_POST['link_url'] ?? '')),
            ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
        ];
        if ($values[0] === '' || $values[1] === '' || $values[4] === '') {
            flash('error', 'Page, section, and content are required.');
            redirect('/admin/page-content' . ($id ? '?edit=' . $id : ''));
        }
        if ($id) {
            $values[] = $id;
            db()->prepare('UPDATE page_content SET page_key=?, section_key=?, eyebrow=?, heading=?, content=?, image_path=?, image_alt=?, link_label=?, link_url=?, status=? WHERE id=?')->execute($values);
        } else {
            db()->prepare('INSERT INTO page_content (page_key, section_key, eyebrow, heading, content, image_path, image_alt, link_label, link_url, status) VALUES (?,?,?,?,?,?,?,?,?,?)')->execute($values);
            $id = (int) db()->lastInsertId();
        }
        audit('save', 'page_content', $id);
        flash('success', 'Page content saved.');
        redirect('/admin/page-content?edit=' . $id);
    }
    $sections = query_all('SELECT * FROM page_content ORDER BY page_key, section_key');
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM page_content WHERE id = ?', [(int) $_GET['edit']]) : ($sections[0] ?? null);
    render('admin/page-content', compact('sections', 'editing') + ['title' => 'Page content'], 'admin');
}

function admin_grants(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'submitted');
        $allowed = ['submitted', 'in_review', 'shortlisted', 'declined', 'awarded'];
        if (!in_array($status, $allowed, true)) {
            $status = 'submitted';
        }
        db()->prepare('UPDATE grant_applications SET status = ?, assigned_reviewer = ?, internal_notes = ? WHERE id = ?')->execute([
            $status,
            !empty($_POST['assigned_reviewer']) ? (int) $_POST['assigned_reviewer'] : null,
            trim((string) ($_POST['internal_notes'] ?? '')),
            $id,
        ]);
        audit('status:' . $status, 'grant_application', $id);
        flash('success', 'Grant application review saved.');
        redirect('/admin/grant-applications?view=' . $id);
    }
    $applications = query_all(
        'SELECT g.*, COALESCE(gf.title, e.title) AS event_title, a.name AS reviewer_name
         FROM grant_applications g
         JOIN events e ON e.id = g.event_id
         LEFT JOIN grant_forms gf ON gf.id = g.form_id
         LEFT JOIN admins a ON a.id = g.assigned_reviewer
         ORDER BY g.created_at DESC'
    );
    $selected = !empty($_GET['view'])
        ? query_one(
            'SELECT g.*, COALESCE(gf.title, e.title) AS event_title
             FROM grant_applications g
             JOIN events e ON e.id = g.event_id
             LEFT JOIN grant_forms gf ON gf.id = g.form_id
             WHERE g.id = ?',
            [(int) $_GET['view']]
        )
        : ($applications[0] ?? null);
    $documents = $selected
        ? query_all('SELECT * FROM grant_application_documents WHERE application_id = ? ORDER BY created_at, id', [$selected['id']])
        : [];
    $admins = query_all(
        "SELECT DISTINCT a.id, a.name
         FROM admins a
         JOIN admin_roles ar ON ar.admin_id = a.id
         JOIN roles r ON r.id = ar.role_id
         LEFT JOIN role_permissions rp ON rp.role_id = r.id
         LEFT JOIN permissions p ON p.id = rp.permission_id
         WHERE a.is_active = 1 AND (r.is_super = 1 OR p.slug = 'grants.manage')
         ORDER BY a.name"
    );
    $counts = query_all('SELECT status, COUNT(*) AS total FROM grant_applications GROUP BY status');
    $counts = array_column($counts, 'total', 'status');
    render('admin/grants', compact('applications', 'selected', 'admins', 'counts', 'documents') + ['title' => 'Grant applications'], 'admin');
}

function admin_grant_forms(): void
{
    if (is_post()) {
        verify_csrf();
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (int) ($_POST['id'] ?? 0);
        if ($action === 'delete') {
            $applicationCount = (int) query_value('SELECT COUNT(*) FROM grant_applications WHERE form_id = ?', [$id]);
            if ($applicationCount > 0) {
                flash('error', 'This form has applications and cannot be deleted. Close it instead.');
                redirect('/admin/grant-forms?edit=' . $id);
            }
            db()->prepare('DELETE FROM grant_forms WHERE id = ?')->execute([$id]);
            audit('delete', 'grant_form', $id);
            flash('success', 'Grant form deleted.');
            redirect('/admin/grant-forms');
        }
        save_grant_form();
    }

    $forms = query_all(
        'SELECT gf.*, e.title AS event_title, COUNT(ga.id) AS application_count
         FROM grant_forms gf
         LEFT JOIN events e ON e.id = gf.event_id
         LEFT JOIN grant_applications ga ON ga.form_id = gf.id
         GROUP BY gf.id
         ORDER BY gf.created_at DESC'
    );
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM grant_forms WHERE id = ?', [(int) $_GET['edit']]) : null;
    $fields = $editing ? query_all('SELECT * FROM grant_form_fields WHERE form_id = ? ORDER BY sort_order, id', [$editing['id']]) : [];
    $events = query_all("SELECT id, title, slug FROM events WHERE event_type = 'Grant Program' ORDER BY event_date DESC, title");
    render('admin/grant-forms', compact('forms', 'editing', 'fields', 'events') + ['title' => 'Grant forms'], 'admin');
}

function save_grant_form(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    $eventId = (int) ($_POST['event_id'] ?? 0);
    $intro = trim((string) ($_POST['intro'] ?? ''));
    $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'closed'], true) ? (string) $_POST['status'] : 'draft';
    $fields = json_decode((string) ($_POST['fields_json'] ?? '[]'), true);
    if ($title === '' || $eventId < 1 || $intro === '') {
        flash('error', 'Title, linked grant event, and introduction are required.');
        redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
    }
    if (!is_array($fields) || !$fields) {
        flash('error', 'Add at least one application field.');
        redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
    }
    $event = query_one("SELECT id FROM events WHERE id = ? AND event_type = 'Grant Program' LIMIT 1", [$eventId]);
    if (!$event) {
        flash('error', 'Choose a valid Grant Program event.');
        redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
    }

    $allowedTypes = ['text', 'email', 'tel', 'number', 'textarea', 'select', 'radio', 'file', 'checkbox'];
    $allowedWidths = ['full', 'half', 'third'];
    $normalizedFields = [];
    $usedKeys = [];
    foreach ($fields as $index => $field) {
        if (!is_array($field)) {
            continue;
        }
        $label = trim((string) ($field['label'] ?? ''));
        $fieldKey = strtolower(trim((string) ($field['field_key'] ?? '')));
        $fieldKey = preg_replace('/[^a-z0-9_]+/', '_', $fieldKey) ?: '';
        $fieldKey = trim($fieldKey, '_');
        $sectionTitle = trim((string) ($field['section_title'] ?? ''));
        $sectionKey = slugify((string) ($field['section_key'] ?? $sectionTitle));
        $type = in_array($field['field_type'] ?? '', $allowedTypes, true) ? (string) $field['field_type'] : 'text';
        if ($label === '' || $fieldKey === '' || $sectionTitle === '' || isset($usedKeys[$fieldKey])) {
            flash('error', 'Every field needs a unique key, label, and section.');
            redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
        }
        $usedKeys[$fieldKey] = true;
        $options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|\|/', (string) ($field['options'] ?? '')) ?: [])));
        if (in_array($type, ['select', 'radio'], true) && !$options) {
            flash('error', $label . ' needs at least one option.');
            redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
        }
        $validation = is_array($field['validation'] ?? null) ? $field['validation'] : [];
        $normalizedFields[] = [
            'section_key' => $sectionKey,
            'section_title' => $sectionTitle,
            'field_key' => $fieldKey,
            'label' => $label,
            'field_type' => $type,
            'help_text' => trim((string) ($field['help_text'] ?? '')),
            'placeholder' => trim((string) ($field['placeholder'] ?? '')),
            'options' => $options,
            'validation' => $validation,
            'is_required' => !empty($field['is_required']) ? 1 : 0,
            'width' => in_array($field['width'] ?? '', $allowedWidths, true) ? (string) $field['width'] : 'full',
            'sort_order' => ($index + 1) * 10,
        ];
    }
    if ($status === 'published') {
        $emailField = array_values(array_filter($normalizedFields, static fn (array $field): bool => $field['field_key'] === 'email' && $field['field_type'] === 'email' && $field['is_required'] === 1));
        $nameField = array_values(array_filter($normalizedFields, static fn (array $field): bool => in_array($field['field_key'], ['full_name', 'first_name'], true) && $field['is_required'] === 1));
        if (!$emailField || !$nameField) {
            flash('error', 'Published forms require a required email field and either a required first_name or full_name field.');
            redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
        }
    }

    $slug = slugify((string) ($_POST['slug'] ?: $title));
    $opensAt = trim((string) ($_POST['opens_at'] ?? '')) ?: null;
    $closesAt = trim((string) ($_POST['closes_at'] ?? '')) ?: null;
    if ($opensAt && $closesAt && new DateTimeImmutable($closesAt) <= new DateTimeImmutable($opensAt)) {
        flash('error', 'The closing date must be after the opening date.');
        redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
    }

    db()->beginTransaction();
    try {
        $values = [
            $eventId, $title, $slug, $intro,
            trim((string) ($_POST['eligibility_notice'] ?? '')),
            trim((string) ($_POST['success_message'] ?? '')),
            strtolower(trim((string) ($_POST['notification_email'] ?? ''))),
            $opensAt, $closesAt, $status, isset($_POST['allow_save_progress']) ? 1 : 0,
        ];
        if ($id) {
            $values[] = $id;
            db()->prepare(
                'UPDATE grant_forms SET event_id=?, title=?, slug=?, intro=?, eligibility_notice=?, success_message=?,
                 notification_email=?, opens_at=?, closes_at=?, status=?, allow_save_progress=? WHERE id=?'
            )->execute($values);
            db()->prepare('DELETE FROM grant_form_fields WHERE form_id = ?')->execute([$id]);
        } else {
            db()->prepare(
                'INSERT INTO grant_forms
                 (event_id, title, slug, intro, eligibility_notice, success_message, notification_email, opens_at, closes_at, status, allow_save_progress)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute($values);
            $id = (int) db()->lastInsertId();
        }
        $insert = db()->prepare(
            'INSERT INTO grant_form_fields
             (form_id, section_key, section_title, field_key, label, field_type, help_text, placeholder, options_json, validation_json, is_required, width, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        foreach ($normalizedFields as $field) {
            $insert->execute([
                $id, $field['section_key'], $field['section_title'], $field['field_key'], $field['label'],
                $field['field_type'], $field['help_text'], $field['placeholder'],
                $field['options'] ? json_encode($field['options'], JSON_THROW_ON_ERROR) : null,
                $field['validation'] ? json_encode($field['validation'], JSON_THROW_ON_ERROR) : null,
                $field['is_required'], $field['width'], $field['sort_order'],
            ]);
        }
        db()->prepare('UPDATE events SET external_link = ? WHERE id = ?')->execute(['/grants/' . $slug . '/apply', $eventId]);
        db()->commit();
    } catch (Throwable $exception) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        if ($exception instanceof PDOException && ($exception->errorInfo[1] ?? 0) === 1062) {
            flash('error', 'That URL slug or linked event is already used by another grant form.');
            redirect('/admin/grant-forms' . ($id ? '?edit=' . $id : '?new=1'));
        }
        throw $exception;
    }

    audit('save', 'grant_form', $id);
    flash('success', 'Grant form saved.');
    redirect('/admin/grant-forms?edit=' . $id);
}

function admin_grant_document(int $id): void
{
    $document = query_one('SELECT * FROM grant_application_documents WHERE id = ? LIMIT 1', [$id]);
    if (!$document) {
        http_response_code(404);
        render('errors/404', ['title' => 'Document not found', 'adminError' => true], 'admin');
        return;
    }
    try {
        $path = GrantDocumentUploader::absolutePath((string) $document['storage_path']);
    } catch (RuntimeException) {
        http_response_code(404);
        render('errors/404', ['title' => 'Document not found', 'adminError' => true], 'admin');
        return;
    }
    audit('view_document:' . $id, 'grant_application', (int) $document['application_id']);
    header('Content-Type: ' . $document['mime_type']);
    header('Content-Length: ' . (string) filesize($path));
    header('Content-Disposition: attachment; filename="' . addcslashes((string) $document['original_name'], "\"\\") . '"');
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

function admin_users(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $existing = $id ? query_one('SELECT * FROM admins WHERE id = ?', [$id]) : null;
        if ($id && !$existing) {
            flash('error', 'Administrator account not found.');
            redirect('/admin/users');
        }
        if ($existing && !can_manage_admin((int) $existing['id'])) {
            http_response_code(403);
            render('errors/403', ['title' => 'Access denied'], 'admin');
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        $roleIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['role_ids'] ?? [])))));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Name and a valid email address are required.');
            redirect('/admin/users' . ($id ? '?edit=' . $id : '?new=1'));
        }
        if ((!$existing || $password !== '') && mb_strlen($password) < 12) {
            flash('error', 'Passwords must contain at least 12 characters.');
            redirect('/admin/users' . ($id ? '?edit=' . $id : '?new=1'));
        }
        if ((int) query_value('SELECT COUNT(*) FROM admins WHERE email = ? AND id <> ?', [$email, $id]) > 0) {
            flash('error', 'Another administrator already uses this email address.');
            redirect('/admin/users' . ($id ? '?edit=' . $id : '?new=1'));
        }

        $assignable = array_column(assignable_roles(), null, 'id');
        $isSelf = $existing && (int) $existing['id'] === (int) auth_user()['id'];
        if ($isSelf) {
            $active = 1;
            $roleIds = array_map('intval', array_column(
                query_all('SELECT role_id FROM admin_roles WHERE admin_id = ?', [$existing['id']]),
                'role_id'
            ));
        }
        if (!$roleIds) {
            flash('error', 'Assign at least one role.');
            redirect('/admin/users' . ($id ? '?edit=' . $id : '?new=1'));
        }
        if (!$isSelf) {
            foreach ($roleIds as $roleId) {
                if (!isset($assignable[$roleId])) {
                    flash('error', 'You cannot assign one of the selected roles.');
                    redirect('/admin/users' . ($id ? '?edit=' . $id : '?new=1'));
                }
            }
        }

        $existingIsSuper = $existing ? admin_is_super((int) $existing['id']) : false;
        $newIsSuper = (int) query_value(
            'SELECT COUNT(*) FROM roles WHERE is_super = 1 AND id IN (' . implode(',', array_fill(0, count($roleIds), '?')) . ')',
            $roleIds
        ) > 0;
        if ($existingIsSuper && (!$newIsSuper || !$active) && active_super_admin_count() <= 1) {
            flash('error', 'The final active super administrator cannot be deactivated or demoted.');
            redirect('/admin/users?edit=' . $id);
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($existing) {
                $params = [$name, $email, $active, $id];
                $sql = "UPDATE admins SET name = ?, email = ?, is_active = ?, role = 'rbac'";
                if ($password !== '') {
                    $sql .= ', password_hash = ?';
                    array_splice($params, 3, 0, [password_hash($password, PASSWORD_DEFAULT)]);
                }
                $sql .= ' WHERE id = ?';
                $pdo->prepare($sql)->execute($params);
            } else {
                $pdo->prepare(
                    "INSERT INTO admins (name, email, password_hash, role, is_active) VALUES (?, ?, ?, 'rbac', ?)"
                )->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $active]);
                $id = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM admin_roles WHERE admin_id = ?')->execute([$id]);
            $roleStmt = $pdo->prepare('INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)');
            foreach ($roleIds as $roleId) {
                $roleStmt->execute([$id, $roleId]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        audit($existing ? 'update' : 'create', 'admin_user', $id);
        flash('success', $existing ? 'Administrator updated.' : 'Administrator created. Share the temporary password securely.');
        redirect('/admin/users?edit=' . $id);
    }

    $users = query_all(
        "SELECT a.*, GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS role_names
         FROM admins a
         LEFT JOIN admin_roles ar ON ar.admin_id = a.id
         LEFT JOIN roles r ON r.id = ar.role_id
         GROUP BY a.id ORDER BY a.name"
    );
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM admins WHERE id = ?', [(int) $_GET['edit']]) : null;
    if ($editing && !can_manage_admin((int) $editing['id'])) {
        http_response_code(403);
        render('errors/403', ['title' => 'Access denied'], 'admin');
        return;
    }
    $editingRoleIds = $editing
        ? array_map('intval', array_column(query_all('SELECT role_id FROM admin_roles WHERE admin_id = ?', [$editing['id']]), 'role_id'))
        : [];
    $roles = assignable_roles();
    render('admin/users', compact('users', 'editing', 'editingRoleIds', 'roles') + ['title' => 'Administrators'], 'admin');
}

function admin_roles(): void
{
    if (is_post()) {
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $action = (string) ($_POST['action'] ?? 'save');
        $existing = $id ? query_one('SELECT * FROM roles WHERE id = ?', [$id]) : null;
        if ($id && !$existing) {
            flash('error', 'Role not found.');
            redirect('/admin/roles');
        }
        if ($existing && !can_manage_role((int) $existing['id'])) {
            http_response_code(403);
            render('errors/403', ['title' => 'Access denied'], 'admin');
            return;
        }
        if ($action === 'delete') {
            if (!$existing || (int) $existing['is_system'] === 1) {
                flash('error', 'System roles cannot be deleted.');
                redirect('/admin/roles' . ($id ? '?edit=' . $id : ''));
            }
            if ((int) query_value('SELECT COUNT(*) FROM admin_roles WHERE role_id = ?', [$id]) > 0) {
                flash('error', 'Remove this role from every administrator before deleting it.');
                redirect('/admin/roles?edit=' . $id);
            }
            db()->prepare('DELETE FROM roles WHERE id = ?')->execute([$id]);
            audit('delete', 'role', $id);
            flash('success', 'Role deleted.');
            redirect('/admin/roles');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['permission_ids'] ?? [])))));
        if ($name === '') {
            flash('error', 'Role name is required.');
            redirect('/admin/roles' . ($id ? '?edit=' . $id : '?new=1'));
        }
        $availablePermissions = array_column(manageable_permissions(), null, 'id');
        foreach ($permissionIds as $permissionId) {
            if (!isset($availablePermissions[$permissionId])) {
                flash('error', 'You cannot grant one of the selected permissions.');
                redirect('/admin/roles' . ($id ? '?edit=' . $id : '?new=1'));
            }
        }
        $dashboardPermission = query_one("SELECT id FROM permissions WHERE slug = 'dashboard.view' LIMIT 1");
        if ($dashboardPermission && isset($availablePermissions[(int) $dashboardPermission['id']])) {
            $permissionIds[] = (int) $dashboardPermission['id'];
            $permissionIds = array_values(array_unique($permissionIds));
        }

        $slug = $existing ? $existing['slug'] : slugify($name);
        if ((int) query_value('SELECT COUNT(*) FROM roles WHERE (name = ? OR slug = ?) AND id <> ?', [$name, $slug, $id]) > 0) {
            flash('error', 'A role with this name already exists.');
            redirect('/admin/roles' . ($id ? '?edit=' . $id : '?new=1'));
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($existing) {
                $pdo->prepare('UPDATE roles SET name = ?, description = ? WHERE id = ?')->execute([$name, $description, $id]);
            } else {
                $pdo->prepare('INSERT INTO roles (name, slug, description) VALUES (?, ?, ?)')->execute([$name, $slug, $description]);
                $id = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM role_permissions WHERE role_id = ?')->execute([$id]);
            $permissionStmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
            foreach ($permissionIds as $permissionId) {
                $permissionStmt->execute([$id, $permissionId]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        audit($existing ? 'update' : 'create', 'role', $id);
        flash('success', 'Role and permissions saved.');
        redirect('/admin/roles?edit=' . $id);
    }

    $roles = query_all(
        'SELECT r.*, COUNT(DISTINCT ar.admin_id) AS user_count, COUNT(DISTINCT rp.permission_id) AS permission_count
         FROM roles r
         LEFT JOIN admin_roles ar ON ar.role_id = r.id
         LEFT JOIN role_permissions rp ON rp.role_id = r.id
         GROUP BY r.id ORDER BY r.is_super DESC, r.is_system DESC, r.name'
    );
    $editing = !empty($_GET['edit']) ? query_one('SELECT * FROM roles WHERE id = ?', [(int) $_GET['edit']]) : null;
    if ($editing && !can_manage_role((int) $editing['id'])) {
        http_response_code(403);
        render('errors/403', ['title' => 'Access denied'], 'admin');
        return;
    }
    $editingPermissionIds = $editing
        ? array_map('intval', array_column(query_all('SELECT permission_id FROM role_permissions WHERE role_id = ?', [$editing['id']]), 'permission_id'))
        : [];
    $permissions = manageable_permissions();
    $permissionGroups = [];
    foreach ($permissions as $permission) {
        $permissionGroups[$permission['group_name']][] = $permission;
    }
    render('admin/roles', compact('roles', 'editing', 'editingPermissionIds', 'permissionGroups') + ['title' => 'Roles and permissions'], 'admin');
}

function assignable_roles(): array
{
    $roles = query_all('SELECT * FROM roles ORDER BY is_super DESC, name');
    return array_values(array_filter($roles, static fn(array $role): bool => can_manage_role((int) $role['id'])));
}

function manageable_permissions(): array
{
    if (is_super_admin()) {
        return query_all('SELECT * FROM permissions ORDER BY group_name, name');
    }
    $slugs = auth_permissions();
    if (!$slugs) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($slugs), '?'));
    return query_all("SELECT * FROM permissions WHERE slug IN ({$placeholders}) ORDER BY group_name, name", $slugs);
}

function can_manage_role(int $roleId): bool
{
    $role = query_one('SELECT * FROM roles WHERE id = ?', [$roleId]);
    if (!$role) {
        return false;
    }
    if (is_super_admin()) {
        return true;
    }
    if ((int) $role['is_super'] === 1) {
        return false;
    }
    if ((int) query_value('SELECT COUNT(*) FROM admin_roles WHERE admin_id = ? AND role_id = ?', [auth_user()['id'], $roleId]) > 0) {
        return false;
    }
    $rolePermissions = array_column(
        query_all('SELECT p.slug FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role_id = ?', [$roleId]),
        'slug'
    );
    return count(array_diff($rolePermissions, auth_permissions())) === 0;
}

function can_manage_admin(int $adminId): bool
{
    if ($adminId === (int) auth_user()['id']) {
        return true;
    }
    if (is_super_admin()) {
        return true;
    }
    if (admin_is_super($adminId)) {
        return false;
    }
    $roleIds = array_map('intval', array_column(query_all('SELECT role_id FROM admin_roles WHERE admin_id = ?', [$adminId]), 'role_id'));
    foreach ($roleIds as $roleId) {
        if (!can_manage_role($roleId)) {
            return false;
        }
    }
    return true;
}

function admin_is_super(int $adminId): bool
{
    return (int) query_value(
        'SELECT COUNT(*) FROM admin_roles ar JOIN roles r ON r.id = ar.role_id WHERE ar.admin_id = ? AND r.is_super = 1',
        [$adminId]
    ) > 0;
}

function active_super_admin_count(): int
{
    return (int) query_value(
        'SELECT COUNT(DISTINCT a.id)
         FROM admins a JOIN admin_roles ar ON ar.admin_id = a.id JOIN roles r ON r.id = ar.role_id
         WHERE a.is_active = 1 AND r.is_super = 1'
    );
}

function audit(string $action, ?string $entityType, ?int $entityId): void
{
    $user = auth_user();
    $stmt = db()->prepare('INSERT INTO audit_log (admin_id, action, entity_type, entity_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user['id'] ?? null, $action, $entityType, $entityId]);
}
