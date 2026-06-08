<?php

declare(strict_types=1);

final class Request
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public static function allGet(): array
    {
        return $_GET;
    }

    public static function allPost(): array
    {
        return $_POST;
    }

    public static function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function inputString(array $source, string $key, int $maxLength = 0): ?string
    {
        $value = $source[$key] ?? null;
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $maxLength > 0 ? mb_substr($value, 0, $maxLength) : $value;
    }

    public static function inputInt(array $source, string $key): ?int
    {
        $value = $source[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT);
        return $filtered === false ? null : (int) $filtered;
    }

    public static function inputFloat(array $source, string $key): ?float
    {
        $value = $source[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);
        $normalized = str_replace([' ', "\xc2\xa0"], '', $normalized);

        $commaPos = strrpos($normalized, ',');
        $dotPos = strrpos($normalized, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($commaPos !== false) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        $filtered = filter_var($normalized, FILTER_VALIDATE_FLOAT);
        return $filtered === false ? null : (float) $filtered;
    }
}
