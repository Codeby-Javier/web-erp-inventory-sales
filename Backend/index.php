<?php

declare(strict_types=1);

const BASE_PATH = __DIR__;

require BASE_PATH . '/config/env.php';
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

spl_autoload_register(static function (string $className): void {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    
    // Core classes are in core/
    $corePath = BASE_PATH . '/core/' . $className . '.php';
    if (is_file($corePath)) {
        require_once $corePath;
        return;
    }

    $directories = [
        BASE_PATH . '/app/Handlers/',
        BASE_PATH . '/app/Models/',
        BASE_PATH . '/app/Services/',
    ];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) continue;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getFilename() === basename($className) . '.php') {
                require_once $fileInfo->getPathname();
                return;
            }
        }
    }
});

try {
    Auth::startSession();
    (new App())->run();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    error_log($exception->getTraceAsString());
    http_response_code(500);

    if (APP_ENV === 'development') {
        echo '<pre>';
        echo htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . PHP_EOL . PHP_EOL;
        echo htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    } else {
        echo 'Terjadi kesalahan pada sistem.';
    }
}
