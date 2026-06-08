<?php

declare(strict_types=1);

final class SettingHandler extends Controller
{
    public function index(): void
    {
        Auth::requireAnyRole(['admin', 'manager']);
        $this->render('setting/index', [
            'flash' => $this->flash(),
            'settings' => $this->db()->fetchAll('SELECT * FROM settings ORDER BY key_name ASC'),
            'csrfField' => Csrf::field(),
        ]);
    }

    public function update(): void
    {
        Auth::requireAnyRole(['admin', 'manager']);
        $this->validateCsrfOrFail(Request::post('csrf_token'));
        $settings = Request::post('settings', []);
        if (!is_array($settings)) {
            Helper::flashSet('error', 'Data setting tidak valid');
            $this->redirect('setting');
        }

        foreach ($settings as $id => $value) {
            $this->db()->update('settings', ['value' => (string) $value], 'id = ?', [(int) $id]);
        }

        Helper::flashSet('success', 'Setting berhasil diperbarui');
        $this->redirect('setting');
    }
}