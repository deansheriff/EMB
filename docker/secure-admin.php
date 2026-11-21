<?php
declare(strict_types=1);

/**
 * Container start-up guard: replaces the publicly documented seed password
 * on any administrator account that still uses it.
 *
 * - If ADMIN_INITIAL_PASSWORD is set (12+ characters), that password is used.
 * - Otherwise a random temporary password is generated and printed once to
 *   the container log so the owner can sign in and change it.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$configured = (string) env('ADMIN_INITIAL_PASSWORD', '');
if ($configured !== '' && mb_strlen($configured) < 12) {
    fwrite(STDERR, "ADMIN_INITIAL_PASSWORD must contain at least 12 characters; generating a random password instead.\n");
    $configured = '';
}

$admins = query_all('SELECT id, email, password_hash FROM admins');
foreach ($admins as $admin) {
    if (!password_verify(DEFAULT_ADMIN_PASSWORD, (string) $admin['password_hash'])) {
        continue;
    }
    $password = $configured !== '' ? $configured : rtrim(strtr(base64_encode(random_bytes(15)), '+/', 'Xy'), '=');
    db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    if ($configured !== '') {
        echo "Replaced the default password for {$admin['email']} with ADMIN_INITIAL_PASSWORD.\n";
    } else {
        echo "================================================================\n";
        echo "Temporary administrator password for {$admin['email']}:\n";
        echo "  {$password}\n";
        echo "Sign in and change it under Admin -> Administrators.\n";
        echo "================================================================\n";
    }
}
