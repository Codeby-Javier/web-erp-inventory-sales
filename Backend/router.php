<?php

declare(strict_types=1);

$requestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$filePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $requestPath);

if ($requestPath !== '/' && is_file($filePath)) {
    return false;
}

require __DIR__ . '/index.php';
