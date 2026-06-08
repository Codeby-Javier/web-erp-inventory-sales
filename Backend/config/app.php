<?php

declare(strict_types=1);

define('APP_NAME', 'ERP System');
if (!defined('APP_URL')) {
    $envAppUrl = getenv('APP_URL') ?: '';

    if ($envAppUrl !== '') {
        define('APP_URL', rtrim($envAppUrl, '/'));
    } else {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        $scriptDir = $scriptDir === '/' || $scriptDir === '.' ? '' : rtrim($scriptDir, '/');

        define('APP_URL', $scheme . '://' . $host . $scriptDir);
    }
}
define('APP_ENV', 'development');
define('TIMEZONE', 'Asia/Jakarta');
define('SESSION_NAME', 'erp_session');
define('SESSION_LIFETIME', 7200);

date_default_timezone_set(TIMEZONE);
