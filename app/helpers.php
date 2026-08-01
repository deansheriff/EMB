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
    return url('assets/' . ltrim($path, '/'));
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
    }
    return $settings[$key] ?? $default;
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
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    return true;
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
