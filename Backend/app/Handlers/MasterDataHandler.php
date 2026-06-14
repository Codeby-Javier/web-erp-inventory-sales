<?php

declare(strict_types=1);

abstract class MasterDataHandler extends Controller
{
    protected string $table;
    protected string $title;
    protected string $viewFolder;

    // Optional extra fields that subclasses can override
    protected array $extraFields = [];

    public function index(): void
    {
        Auth::requireLogin();
        $items = $this->db()->fetchAll("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 200");
        $this->render($this->viewFolder . '/index', ['items' => $items, 'title' => $this->title, 'flash' => $this->flash()]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->render($this->viewFolder . '/form', [
            'title' => $this->title,
            'mode' => 'create',
            'item' => [],
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $post = Request::allPost();
        $name = trim((string) ($post['name'] ?? ''));

        if ($name === '') {
            $_SESSION['form_errors'] = ['name' => 'Nama wajib diisi'];
            $_SESSION['form_old'] = $post;
            $this->redirect($this->viewFolder . '/create');
        }

        $data = ['name' => $name];

        // Handle is_active if table has it
        if ($this->tableHasColumn('is_active')) {
            $data['is_active'] = (int) ($post['is_active'] ?? 1);
        }

        // Handle description if table has it
        if ($this->tableHasColumn('description')) {
            $data['description'] = trim((string) ($post['description'] ?? ''));
        }

        // Handle extra fields (for supplier, customer, etc)
        foreach ($this->extraFields as $field) {
            if (array_key_exists($field, $post)) {
                $data[$field] = trim((string) ($post[$field] ?? '')) ?: null;
            }
        }

        // Handle name_plural for units
        if ($this->tableHasColumn('name_plural')) {
            $data['name_plural'] = trim((string) ($post['name_plural'] ?? '')) ?: null;
        }

        $this->db()->insert($this->table, $data);
        Helper::flashSet('success', $this->title . ' berhasil ditambahkan');
        $this->redirect($this->viewFolder);
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();
        $item = $this->db()->fetchOne("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
        if ($item === null) {
            $this->abortNotFound();
        }

        $this->render($this->viewFolder . '/form', [
            'title' => $this->title,
            'mode' => 'edit',
            'item' => $item,
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $item = $this->db()->fetchOne("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
        if ($item === null) {
            $this->abortNotFound();
        }

        $post = Request::allPost();
        $name = trim((string) ($post['name'] ?? ''));

        if ($name === '') {
            $_SESSION['form_errors'] = ['name' => 'Nama wajib diisi'];
            $_SESSION['form_old'] = $post;
            $this->redirect($this->viewFolder . '/edit/' . $id);
        }

        $data = ['name' => $name];

        if (array_key_exists('is_active', $item)) {
            $data['is_active'] = (int) ($post['is_active'] ?? $item['is_active'] ?? 1);
        }

        if (array_key_exists('description', $item)) {
            $data['description'] = trim((string) ($post['description'] ?? '')) ?: null;
        }

        foreach ($this->extraFields as $field) {
            if (array_key_exists($field, $item)) {
                $data[$field] = trim((string) ($post[$field] ?? '')) ?: null;
            }
        }

        if (array_key_exists('name_plural', $item)) {
            $data['name_plural'] = trim((string) ($post['name_plural'] ?? '')) ?: null;
        }

        $this->db()->update($this->table, $data, 'id = ?', [$id]);
        Helper::flashSet('success', $this->title . ' berhasil diperbarui');
        $this->redirect($this->viewFolder);
    }

    public function delete(int $id): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $item = $this->db()->fetchOne("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
        if ($item === null) {
            $this->abortNotFound();
        }

        try {
            if (array_key_exists('is_active', $item)) {
                $this->db()->update($this->table, ['is_active' => 0], 'id = ?', [$id]);
            } else {
                $this->db()->delete($this->table, 'id = ?', [$id]);
            }
            Helper::flashSet('success', $this->title . ' berhasil dihapus');
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            Helper::flashSet('error', 'Gagal menghapus: ' . $e->getMessage());
        }

        $this->redirect($this->viewFolder);
    }

    private function tableHasColumn(string $column): bool
    {
        try {
            $result = $this->db()->fetchOne(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$this->table, $column]
            );
            return $result !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}