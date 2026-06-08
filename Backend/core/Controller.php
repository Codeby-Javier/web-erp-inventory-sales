<?php

declare(strict_types=1);

abstract class Controller
{
    protected ?Database $db = null;
    protected array $data = [];

    public function __construct()
    {
        Auth::startSession();
    }

    protected function db(): Database
    {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }

        return $this->db;
    }

    protected function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data, EXTR_SKIP);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            exit;
        }

        require $viewFile;
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function flash(): array
    {
        return Helper::flashGet();
    }

    protected function abortNotFound(): never
    {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
        exit;
    }

    protected function validateCsrfOrFail(?string $token): void
    {
        if (!Csrf::verify($token)) {
            Helper::flashSet('error', 'Request tidak valid');
            Response::back();
        }
    }
}
