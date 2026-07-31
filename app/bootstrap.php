<?php
declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';
const PUBLIC_PATH = BASE_PATH . '/public';

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/MediaUploader.php';
require_once __DIR__ . '/GrantDocumentUploader.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/PaystackClient.php';
require_once __DIR__ . '/PublicController.php';
require_once __DIR__ . '/AdminController.php';

load_env(BASE_PATH . '/.env');

date_default_timezone_set(env('APP_TIMEZONE', 'Africa/Lagos'));

$secure = filter_var(env('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN);
session_name('emb_admin');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; img-src 'self' https: data:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com data:; script-src 'self'; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

set_exception_handler(static function (Throwable $exception): void {
    $message = sprintf(
        "[%s] %s in %s:%d\n%s\n",
        date('c'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    if (is_dir(BASE_PATH . '/storage/logs')) {
        error_log($message, 3, BASE_PATH . '/storage/logs/app.log');
    }
    http_response_code(500);
    if (filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN)) {
        echo '<pre>' . e($message) . '</pre>';
    } else {
        render('errors/500', ['title' => 'Something went wrong']);
    }
});
