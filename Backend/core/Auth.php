<?php

declare(strict_types=1);

final class Auth
{
    private static bool $started = false;

    public static function startSession(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $sessionPath = __DIR__ . '/../sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        session_name(SESSION_NAME);
        session_save_path($sessionPath);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
        self::$started = true;
    }

    public static function login(string $username, string $password): bool
    {
        self::startSession();
        $db = Database::getInstance();

        try {
            $user = $db->fetchOne(
                'SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1',
                [$username]
            );

            if ($user === null || !password_verify($password, (string) ($user['password'] ?? ''))) {
                return false;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = (string) $user['username'];
            $_SESSION['full_name'] = (string) ($user['full_name'] ?? $user['username']);
            $_SESSION['role'] = (string) $user['role'];
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => (string) $user['username'],
                'full_name' => (string) ($user['full_name'] ?? $user['username']),
                'role' => (string) $user['role'],
            ];

            $db->query('UPDATE users SET last_login = NOW() WHERE id = ?', [(int) $user['id']]);
            return true;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'] ?? '',
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::startSession();
        $user = $_SESSION['user'] ?? null;
        return is_array($user) ? $user : null;
    }

    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user !== null && ($user['role'] ?? null) === $role;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Helper::flashSet('error', 'Silakan login terlebih dahulu.');
            Response::redirect('auth/login');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (!self::hasRole($role)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    public static function requireAnyRole(array $roles): void
    {
        self::requireLogin();
        $currentRole = self::user()['role'] ?? null;
        if (!in_array($currentRole, $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}