<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$roleId = 0;
$adminId = 0;
$exitCode = 1;
$email = 'qa.rbac.' . bin2hex(random_bytes(4)) . '@example.com';
$password = 'TemporaryPass!2026';

try {
    db()->prepare(
        "INSERT INTO roles (name, slug, description) VALUES ('QA Appointment Manager', ?, 'Temporary automated RBAC test role.')"
    )->execute(['qa-appointment-manager-' . bin2hex(random_bytes(3))]);
    $roleId = (int) db()->lastInsertId();
    db()->prepare(
        "INSERT INTO role_permissions (role_id, permission_id)
         SELECT ?, id FROM permissions WHERE slug IN ('dashboard.view', 'appointments.manage')"
    )->execute([$roleId]);

    db()->prepare(
        "INSERT INTO admins (name, email, password_hash, role, is_active)
         VALUES ('QA RBAC User', ?, ?, 'rbac', 1)"
    )->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
    $adminId = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO admin_roles (admin_id, role_id) VALUES (?, ?)')->execute([$adminId, $roleId]);

    $loggedIn = attempt_login($email, $password);
    $passed = $loggedIn
        && can('dashboard.view')
        && can('appointments.manage')
        && !can('events.manage')
        && !can('settings.manage')
        && !can('users.manage')
        && !is_super_admin();

    echo json_encode([
        'passed' => $passed,
        'logged_in' => $loggedIn,
        'allowed' => [
            'dashboard.view' => can('dashboard.view'),
            'appointments.manage' => can('appointments.manage'),
        ],
        'denied' => [
            'events.manage' => !can('events.manage'),
            'settings.manage' => !can('settings.manage'),
            'users.manage' => !can('users.manage'),
        ],
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
    $exitCode = $passed ? 0 : 1;
} finally {
    if ($adminId > 0) {
        db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$adminId]);
    }
    if ($roleId > 0) {
        db()->prepare('DELETE FROM roles WHERE id = ?')->execute([$roleId]);
    }
}
exit($exitCode);
