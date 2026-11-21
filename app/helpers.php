<?php
declare(strict_types=1);

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    return $value === false || $value === null || $value === '' ? $default : $value;
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', '127.0.0.1'),
        env('DB_PORT', '3306'),
        env('DB_DATABASE', 'emb_chronicles')
    );
    $pdo = new PDO($dsn, (string) env('DB_USERNAME', 'root'), (string) env('DB_PASSWORD', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $relative = 'assets/' . ltrim($path, '/');
    $absolute = PUBLIC_PATH . '/' . $relative;
    $version = is_file($absolute) ? '?v=' . filemtime($absolute) : '';
    return url($relative) . $version;
}

function media_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return url(ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function render(string $view, array $data = [], string $layout = 'public'): void
{
    extract($data, EXTR_SKIP);
    $viewFile = BASE_PATH . '/views/' . $view . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException('View not found: ' . $view);
    }
    ob_start();
    require $viewFile;
    $content = (string) ob_get_clean();
    require BASE_PATH . '/views/layouts/' . $layout . '.php';
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return '/' . trim($path, '/');
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = (string) ($_POST['_csrf'] ?? '');
    if ($token === '' || !hash_equals((string) ($_SESSION['_csrf'] ?? ''), $token)) {
        http_response_code(419);
        render('errors/419', ['title' => 'Session expired']);
        exit;
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = compact('type', 'message');
}

function pull_flashes(): array
{
    $items = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $items;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function store_old(array $data): void
{
    unset($data['_csrf'], $data['website']);
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function slugify(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($value)) ?: '';
    return trim($value, '-') ?: bin2hex(random_bytes(4));
}

function setting(string $key, mixed $default = ''): mixed
{
    static $settings;
    if ($settings === null) {
        try {
            $settings = db()->query('SELECT `key`, `value` FROM site_settings')
                ->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable) {
            $settings = [];
        }
        if ($settings) {
            try {
                $settings = apply_env_setting_defaults($settings);
            } catch (Throwable) {
                // Keep the saved settings if the one-time defaults cannot be written.
            }
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Copies SMTP and Paystack credentials from the environment into site settings
 * the first time they are found and the matching settings are still empty.
 * A sentinel row makes this a one-time default, so later admin changes win.
 */
function apply_env_setting_defaults(array $settings): array
{
    $groups = [
        'smtp' => [
            'trigger' => 'SMTP_HOST',
            'empty_setting' => 'smtp_host',
            'map' => [
                'smtp_host' => ['SMTP_HOST', 'text'],
                'smtp_port' => ['SMTP_PORT', 'number'],
                'smtp_encryption' => ['SMTP_ENCRYPTION', 'text'],
                'smtp_username' => ['SMTP_USERNAME', 'text'],
                'smtp_password' => ['SMTP_PASSWORD', 'secret'],
                'smtp_from_email' => ['SMTP_FROM_EMAIL', 'email'],
                'smtp_from_name' => ['SMTP_FROM_NAME', 'text'],
                'smtp_admin_email' => ['SMTP_ADMIN_EMAIL', 'email'],
            ],
            'flag' => ['smtp_enabled', 'SMTP_ENABLED'],
        ],
        'paystack' => [
            'trigger' => 'PAYSTACK_SECRET_KEY',
            'empty_setting' => 'paystack_secret_key',
            'map' => [
                'paystack_public_key' => ['PAYSTACK_PUBLIC_KEY', 'text'],
                'paystack_secret_key' => ['PAYSTACK_SECRET_KEY', 'secret'],
            ],
            'flag' => ['paystack_enabled', 'PAYSTACK_ENABLED'],
        ],
    ];
    foreach ($groups as $name => $group) {
        $sentinel = 'env_defaults_' . $name . '_applied';
        if (isset($settings[$sentinel]) || trim((string) env($group['trigger'], '')) === '') {
            continue;
        }
        $updates = [];
        if (trim((string) ($settings[$group['empty_setting']] ?? '')) === '') {
            foreach ($group['map'] as $settingKey => [$envKey, $type]) {
                $value = trim((string) env($envKey, ''));
                if ($value !== '') {
                    $updates[$settingKey] = [$value, $type];
                }
            }
            [$flagKey, $flagEnv] = $group['flag'];
            if (filter_var(env($flagEnv, 'false'), FILTER_VALIDATE_BOOLEAN)) {
                $updates[$flagKey] = ['1', 'boolean'];
            }
        }
        $updates[$sentinel] = ['1', 'boolean'];
        $stmt = db()->prepare(
            'INSERT INTO site_settings (`key`, `value`, `type`) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`)'
        );
        foreach ($updates as $settingKey => [$value, $type]) {
            $stmt->execute([$settingKey, $value, $type]);
            $settings[$settingKey] = $value;
        }
    }
    return $settings;
}

function client_ip(): string
{
    if (filter_var(env('TRUST_PROXY', 'false'), FILTER_VALIDATE_BOOLEAN)) {
        $forwarded = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]);
        if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
            return $forwarded;
        }
    }
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function rate_limit_count(string $bucket, string $key, int $windowSeconds): int
{
    try {
        return (int) query_value(
            'SELECT COUNT(*) FROM rate_limits WHERE bucket = ? AND key_hash = ? AND created_at > (NOW() - INTERVAL ' . max(1, $windowSeconds) . ' SECOND)',
            [$bucket, hash('sha256', strtolower($key))]
        );
    } catch (Throwable) {
        // Fail open if the rate_limits table has not been migrated yet.
        return 0;
    }
}

function rate_limit_record(string $bucket, string $key): void
{
    try {
        db()->prepare('INSERT INTO rate_limits (bucket, key_hash) VALUES (?, ?)')
            ->execute([$bucket, hash('sha256', strtolower($key))]);
        if (random_int(1, 50) === 1) {
            db()->exec('DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 2 DAY)');
        }
    } catch (Throwable) {
    }
}

function rate_limit_clear(string $bucket, string $key): void
{
    try {
        db()->prepare('DELETE FROM rate_limits WHERE bucket = ? AND key_hash = ?')
            ->execute([$bucket, hash('sha256', strtolower($key))]);
    } catch (Throwable) {
    }
}

/**
 * Throttles a public form both per browser session (short cooldown) and per
 * client IP address (hourly cap) so scripts cannot bypass it by dropping cookies.
 */
function public_form_throttled(string $form, int $maxPerHour, int $cooldownSeconds = 20): bool
{
    if (form_rate_limited($form, $cooldownSeconds)) {
        return true;
    }
    $bucket = 'form:' . $form;
    $ip = client_ip();
    if (rate_limit_count($bucket, $ip, 3600) >= $maxPerHour) {
        return true;
    }
    rate_limit_record($bucket, $ip);
    return false;
}

function safe_back_url(string $fallback = '/'): string
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $appHost = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);
    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? null;
    if ($referer !== '' && $refererHost && ($refererHost === $appHost || $refererHost === $currentHost)) {
        return $referer;
    }
    return url($fallback);
}

/**
 * Allow-list HTML sanitizer for admin-authored rich text (blog posts, event
 * and service descriptions, page sections). Scripts, embeds, forms, inline
 * styles, event handlers, and unsafe URL schemes are removed.
 */
function safe_html(?string $html): string
{
    $html = (string) $html;
    if (trim($html) === '') {
        return '';
    }
    static $cache = [];
    $cacheKey = md5($html);
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="UTF-8"><div data-safe-root="1">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = (new DOMXPath($document))->query('//div[@data-safe-root="1"]')->item(0);
    if (!$root instanceof DOMElement) {
        return $cache[$cacheKey] = e(strip_tags($html));
    }
    safe_html_clean_children($root);
    $output = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $output .= $document->saveHTML($child);
    }
    return $cache[$cacheKey] = $output;
}

function safe_html_clean_children(DOMNode $parent): void
{
    static $allowed = [
        'p' => [], 'br' => [], 'hr' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'small' => [], 'sub' => [], 'sup' => [], 'span' => [], 'div' => [], 'blockquote' => ['cite'],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'ul' => [], 'ol' => ['start', 'type'], 'li' => [], 'dl' => [], 'dt' => [], 'dd' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'figure' => [], 'figcaption' => [], 'code' => [], 'pre' => [], 'mark' => [], 'abbr' => ['title'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [], 'caption' => [],
        'th' => ['colspan', 'rowspan', 'scope'], 'td' => ['colspan', 'rowspan'],
    ];
    static $dropWithContent = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'input',
        'button', 'select', 'textarea', 'option', 'meta', 'link', 'base', 'svg', 'math', 'template',
        'noscript', 'audio', 'video', 'source', 'track', 'canvas', 'title', 'head',
    ];

    foreach (iterator_to_array($parent->childNodes) as $node) {
        if ($node instanceof DOMText) {
            continue;
        }
        if (!$node instanceof DOMElement) {
            $parent->removeChild($node);
            continue;
        }
        $tag = strtolower($node->tagName);
        if (in_array($tag, $dropWithContent, true)) {
            $parent->removeChild($node);
            continue;
        }
        if (!isset($allowed[$tag])) {
            safe_html_clean_children($node);
            while ($node->firstChild) {
                $parent->insertBefore($node->firstChild, $node);
            }
            $parent->removeChild($node);
            continue;
        }
        $permitted = array_merge($allowed[$tag], ['class']);
        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            if (!in_array($name, $permitted, true)) {
                $node->removeAttribute($attribute->name);
                continue;
            }
            if (in_array($name, ['href', 'src', 'cite'], true)
                && !safe_html_url_allowed($attribute->value, $name === 'href' ? ['http', 'https', 'mailto', 'tel'] : ['http', 'https'])) {
                $node->removeAttribute($attribute->name);
            }
        }
        if ($tag === 'a' && strtolower($node->getAttribute('target')) === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
        safe_html_clean_children($node);
    }
}

function safe_html_url_allowed(string $url, array $schemes): bool
{
    $normalized = preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';
    if (!preg_match('/^([a-z][a-z0-9+.\-]*):/i', $normalized, $matches)) {
        return true;
    }
    return in_array(strtolower($matches[1]), $schemes, true);
}

function page_section(string $page, string $section): ?array
{
    static $cache = [];
    $key = $page . ':' . $section;
    if (!array_key_exists($key, $cache)) {
        $stmt = db()->prepare("SELECT * FROM page_content WHERE page_key = ? AND section_key = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$page, $section]);
        $cache[$key] = $stmt->fetch() ?: null;
    }
    return $cache[$key];
}

function page_content(string $page, string $section, string $default = ''): string
{
    $managedSection = page_section($page, $section);
    return $managedSection ? (string) $managedSection['content'] : $default;
}

function auth_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $user;
    if ($user === null) {
        $stmt = db()->prepare(
            "SELECT a.id, a.name, a.email, a.role, a.is_active, a.last_login_at,
                    GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS role_names
             FROM admins a
             LEFT JOIN admin_roles ar ON ar.admin_id = a.id
             LEFT JOIN roles r ON r.id = ar.role_id
             WHERE a.id = ? AND a.is_active = 1
             GROUP BY a.id LIMIT 1"
        );
        $stmt->execute([(int) $_SESSION['admin_id']]);
        $user = $stmt->fetch() ?: null;
        if ($user) {
            $user['role'] = $user['role_names'] ?: 'No assigned role';
        } else {
            unset($_SESSION['admin_id']);
        }
    }
    return $user;
}

function require_auth(): void
{
    if (!auth_user()) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/admin';
        redirect('/admin/login');
    }
}

function auth_roles(): array
{
    static $roles;
    if ($roles === null) {
        $user = auth_user();
        $roles = $user
            ? query_all(
                'SELECT r.id, r.name, r.slug, r.is_super
                 FROM roles r JOIN admin_roles ar ON ar.role_id = r.id
                 WHERE ar.admin_id = ? ORDER BY r.name',
                [$user['id']]
            )
            : [];
    }
    return $roles;
}

function is_super_admin(): bool
{
    foreach (auth_roles() as $role) {
        if ((int) $role['is_super'] === 1) {
            return true;
        }
    }
    return false;
}

function auth_permissions(): array
{
    static $permissions;
    if ($permissions === null) {
        $user = auth_user();
        if (!$user) {
            $permissions = [];
        } elseif (is_super_admin()) {
            $permissions = query_all('SELECT slug FROM permissions ORDER BY slug');
            $permissions = array_column($permissions, 'slug');
        } else {
            $permissions = query_all(
                'SELECT DISTINCT p.slug
                 FROM permissions p
                 JOIN role_permissions rp ON rp.permission_id = p.id
                 JOIN admin_roles ar ON ar.role_id = rp.role_id
                 WHERE ar.admin_id = ? ORDER BY p.slug',
                [$user['id']]
            );
            $permissions = array_column($permissions, 'slug');
        }
    }
    return $permissions;
}

function can(string $permission): bool
{
    return auth_user() !== null
        && (is_super_admin() || in_array($permission, auth_permissions(), true));
}

function can_any(array $permissions): bool
{
    foreach ($permissions as $permission) {
        if (can($permission)) {
            return true;
        }
    }
    return false;
}

function require_permission(string $permission): void
{
    if (can($permission)) {
        return;
    }
    http_response_code(403);
    render('errors/403', [
        'title' => 'Access denied',
        'requiredPermission' => $permission,
    ], 'admin');
    exit;
}

function attempt_login(string $email, string $password): bool
{
    $stmt = db()->prepare(
        'SELECT a.* FROM admins a
         WHERE a.email = ? AND a.is_active = 1
           AND EXISTS (SELECT 1 FROM admin_roles ar WHERE ar.admin_id = a.id)
         LIMIT 1'
    );
    $stmt->execute([strtolower(trim($email))]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    rate_limit_clear('login_email', strtolower(trim($email)));
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    return true;
}

const DEFAULT_ADMIN_PASSWORD = 'ChangeMe123!';
const LOGIN_MAX_FAILURES_PER_EMAIL = 5;
const LOGIN_MAX_FAILURES_PER_IP = 20;
const LOGIN_FAILURE_WINDOW_SECONDS = 900;

function login_locked_out(string $email): bool
{
    $email = strtolower(trim($email));
    return rate_limit_count('login_email', $email, LOGIN_FAILURE_WINDOW_SECONDS) >= LOGIN_MAX_FAILURES_PER_EMAIL
        || rate_limit_count('login_ip', client_ip(), LOGIN_FAILURE_WINDOW_SECONDS) >= LOGIN_MAX_FAILURES_PER_IP;
}

function record_login_failure(string $email): void
{
    rate_limit_record('login_email', strtolower(trim($email)));
    rate_limit_record('login_ip', client_ip());
}

function logout_admin(): void
{
    unset($_SESSION['admin_id']);
    session_regenerate_id(true);
}

function query_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

function query_value(string $sql, array $params = []): mixed
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function form_rate_limited(string $key, int $seconds = 20): bool
{
    $last = (int) ($_SESSION['_rate'][$key] ?? 0);
    if ($last > 0 && time() - $last < $seconds) {
        return true;
    }
    $_SESSION['_rate'][$key] = time();
    return false;
}

function is_honeypot_clean(): bool
{
    return trim((string) ($_POST['website'] ?? '')) === '';
}

function format_date(?string $date, string $format = 'M j, Y'): string
{
    if (!$date) {
        return 'Date to be announced';
    }
    return (new DateTimeImmutable($date))->format($format);
}

function money_to_subunit(string $amount): int
{
    $normalized = str_replace([',', ' '], '', trim($amount));
    if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
        return 0;
    }
    [$whole, $decimal] = array_pad(explode('.', $normalized, 2), 2, '');
    return ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');
}

function format_money(int $subunit, string $currency = 'NGN'): string
{
    $symbol = strtoupper($currency) === 'NGN' ? '₦' : strtoupper($currency) . ' ';
    return $symbol . number_format($subunit / 100, 2);
}

function new_booking_code(): string
{
    return 'EMB-' . strtoupper(bin2hex(random_bytes(10)));
}

function excerpt(string $html, int $length = 150): string
{
    $text = trim(strip_tags($html));
    return mb_strlen($text) <= $length ? $text : mb_substr($text, 0, $length - 1) . '…';
}
