<?php

declare(strict_types=1);

abstract class MasterDataHandler extends Controller
{
    protected string $table;
    protected string $title;
    protected string $viewFolder;

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
            'csrfField' => Csrf::field(),
            'errors' => Helper::pullErrors(),
            'old' => Helper::pullOld(),
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $name = trim((string) Request::post('name', ''));
        if ($name === '') {
            $_SESSION['form_errors'] = ['name' => 'Nama wajib diisi'];
            $_SESSION['form_old'] = ['name' => $name];
            $this->redirect($this->viewFolder . '/create');
        }

        $data = ['name' => $name];
        if (Request::post('is_active') !== null) {
            $data['is_active'] = (int) Request::post('is_active', 1);
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

        $name = trim((string) Request::post('name', ''));
        if ($name === '') {
            $_SESSION['form_errors'] = ['name' => 'Nama wajib diisi'];
            $_SESSION['form_old'] = ['name' => $name];
            $this->redirect($this->viewFolder . '/edit/' . $id);
        }

        $data = ['name' => $name];
        if (array_key_exists('is_active', $item)) {
            $data['is_active'] = (int) Request::post('is_active', (string) ($item['is_active'] ?? 1));
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

        if (array_key_exists('is_active', $item)) {
            $this->db()->update($this->table, ['is_active' => 0], 'id = ?', [$id]);
        } else {
            $this->db()->delete($this->table, 'id = ?', [$id]);
        }

        Helper::flashSet('success', $this->title . ' berhasil dihapus');
        $this->redirect($this->viewFolder);
    }
}