<?php

declare(strict_types=1);

final class Helper
{
    public static function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public static function formatDate(string $date): string
    {
        $timestamp = strtotime($date);
        return $timestamp === false ? $date : date('d/m/Y', $timestamp);
    }

    public static function formatDatetime(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        return $timestamp === false ? $datetime : date('d/m/Y H:i', $timestamp);
    }

    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function generateProductCode(): string
    {
        $db = Database::getInstance();
        $lastCode = $db->fetchColumn("SELECT code FROM products WHERE code LIKE 'PRD-%' ORDER BY id DESC LIMIT 1");
        $nextNumber = 1;

        if (is_string($lastCode) && preg_match('/PRD-(\d+)/', $lastCode, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return sprintf('PRD-%04d', $nextNumber);
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function flashSet(string $type, string $message): void
    {
        Auth::startSession();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function flashGet(): array
    {
        Auth::startSession();
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return is_array($flash) ? $flash : [];
    }

    public static function pullOld(): array
    {
        Auth::startSession();
        $old = $_SESSION['form_old'] ?? [];
        unset($_SESSION['form_old']);
        return is_array($old) ? $old : [];
    }

    public static function pullErrors(): array
    {
        Auth::startSession();
        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);
        return is_array($errors) ? $errors : [];
    }
}