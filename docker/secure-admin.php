<?php
declare(strict_types=1);

/**
 * Container start-up guard for administrator access.
 *
 * 1. ADMIN_INITIAL_PASSWORD (12+ characters) is applied to the main
 *    administrator whenever its value is new or has changed since the last
 *    deploy. Changing the variable and redeploying therefore resets the
 *    password; leaving it unchanged keeps passwords changed in the dashboard.
 *    The account is ADMIN_EMAIL when set, otherwise admin@embchronicles.com,
 *    otherwise the first super administrator.
 * 2. Any administrator still using the public seed password gets a random
 *    temporary password, printed once to the deployment log.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$deadline = time() + max(5, (int) env('DB_WAIT_TIMEOUT', 60));
while (true) {
    try {
        db();
        break;
    } catch (Throwable $exception) {
        if (time() >= $deadline) {
            fwrite(STDERR, "Administrator password check skipped: the database is not reachable.\n");
            exit(1);
        }
        sleep(2);
    }
}

function admin_bootstrap_clear_login_lockouts(): void
{
    try {
        db()->exec("DELETE FROM rate_limits WHERE bucket IN ('login_email', 'login_ip')");
    } catch (Throwable) {
        // The rate_limits table may not exist before the security migration.
    }
}

function admin_bootstrap_target(): ?array
{
    $email = strtolower(trim((string) env('ADMIN_EMAIL', '')));
    foreach (array_filter([$email, 'admin@embchronicles.com']) as $candidate) {
        $admin = query_one('SELECT id, email FROM admins WHERE email = ? LIMIT 1', [$candidate]);
        if ($admin) {
            return $admin;
        }
    }
    return query_one(
        'SELECT a.id, a.email FROM admins a
         JOIN admin_roles ar ON ar.admin_id = a.id
         JOIN roles r ON r.id = ar.role_id
         WHERE r.is_super = 1 ORDER BY a.id LIMIT 1'
    );
}

$configured = (string) env('ADMIN_INITIAL_PASSWORD', '');
if ($configured !== '' && mb_strlen($configured) < 12) {
    fwrite(STDERR, "ADMIN_INITIAL_PASSWORD is shorter than 12 characters and was ignored.\n");
    $configured = '';
}

if ($configured !== '') {
    $marker = (string) (query_value("SELECT `value` FROM site_settings WHERE `key` = 'admin_initial_password_applied'") ?: '');
    if ($marker !== '' && password_verify($configured, $marker)) {
        echo "ADMIN_INITIAL_PASSWORD is unchanged since the last deploy; keeping the current administrator password.\n";
    } else {
        $target = admin_bootstrap_target();
        if (!$target) {
            fwrite(STDERR, "ADMIN_INITIAL_PASSWORD is set but no administrator account was found. Set ADMIN_EMAIL to an existing admin email.\n");
        } else {
            db()->prepare('UPDATE admins SET password_hash = ?, is_active = 1 WHERE id = ?')
                ->execute([password_hash($configured, PASSWORD_DEFAULT), $target['id']]);
            db()->prepare(
                "INSERT INTO site_settings (`key`, `value`, `type`) VALUES ('admin_initial_password_applied', ?, 'secret')
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`)"
            )->execute([password_hash($configured, PASSWORD_DEFAULT)]);
            admin_bootstrap_clear_login_lockouts();
            echo "Administrator password for {$target['email']} was set from ADMIN_INITIAL_PASSWORD.\n";
        }
    }
}

foreach (query_all('SELECT id, email, password_hash FROM admins') as $admin) {
    if (!password_verify(DEFAULT_ADMIN_PASSWORD, (string) $admin['password_hash'])) {
        continue;
    }
    $password = rtrim(strtr(base64_encode(random_bytes(15)), '+/', 'Xy'), '=');
    db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    admin_bootstrap_clear_login_lockouts();
    echo "================================================================\n";
    echo "Temporary administrator password for {$admin['email']}:\n";
    echo "  {$password}\n";
    echo "Sign in and change it under Admin -> Administrators,\n";
    echo "or set ADMIN_INITIAL_PASSWORD and redeploy.\n";
    echo "================================================================\n";
}
