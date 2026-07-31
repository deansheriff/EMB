<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$path = request_path();

if ($path === '/sitemap.xml') {
    public_sitemap();
    exit;
}

if (str_starts_with($path, '/admin')) {
    admin_dispatch($path);
    exit;
}

public_dispatch($path);

