<?php

declare(strict_types=1);

final class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
        exit;
    }

    public static function back(): never
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL));
        exit;
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}