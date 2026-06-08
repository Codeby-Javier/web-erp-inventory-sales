<?php

declare(strict_types=1);

final class App
{
    public function run(): void
    {
        $path = '/' . trim((string) ($_GET['url'] ?? $this->resolveUrlFromRequest()), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        $route = $this->matchRoute(Request::method(), $path);

        if ($route === null) {
            $this->renderNotFound();
        }

        [$controllerClass, $method] = explode('@', $route['handler'], 2);
        $params = $route['params'];

        if (!class_exists($controllerClass)) {
            $this->renderNotFound();
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            $this->renderNotFound();
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function matchRoute(string $method, string $path): ?array
    {
        $routes = require __DIR__ . '/../routes.php';

        foreach ($routes as [$routeMethod, $routePath, $handler]) {
            if (strtoupper($routeMethod) !== strtoupper($method)) {
                continue;
            }

            $paramNames = [];
            $pattern = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static function (array $matches) use (&$paramNames): string {
                    $paramNames[] = $matches[1];
                    return '([^/]+)';
                },
                rtrim($routePath, '/') ?: '/'
            );

            if (!is_string($pattern)) {
                continue;
            }

            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches) !== 1) {
                continue;
            }

            array_shift($matches);

            return [
                'handler' => $handler,
                'params' => array_map(
                    static fn (string $value): int|string => ctype_digit($value) ? (int) $value : $value,
                    $matches
                ),
            ];
        }

        return null;
    }

    private function resolveUrlFromRequest(): string
    {
        $requestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '.' && str_starts_with($requestPath, $basePath)) {
            $requestPath = (string) substr($requestPath, strlen($basePath));
        }

        return trim($requestPath, '/');
    }

    private function renderNotFound(): never
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
        exit;
    }
}
