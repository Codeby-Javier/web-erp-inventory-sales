<?php

declare(strict_types=1);

final class Csrf
{
    public static function generate(): string
    {
        Auth::startSession();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return (string) $_SESSION['csrf_token'];
    }

    public static function token(): string
    {
        Auth::startSession();
        return isset($_SESSION['csrf_token']) ? (string) $_SESSION['csrf_token'] : self::generate();
    }

    public static function verify(?string $token): bool
    {
        Auth::startSession();
        if ($token === null || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals((string) $_SESSION['csrf_token'], $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . Helper::e(self::token()) . '">';
    }
}