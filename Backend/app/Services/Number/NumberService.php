<?php

declare(strict_types=1);

final class NumberService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function nextSONumber(): string
    {
        return $this->nextNumber('so_counter', 'so_prefix', 'SO');
    }

    public function nextPONumber(): string
    {
        return $this->nextNumber('po_counter', 'po_prefix', 'PO');
    }

    private function nextNumber(string $counterKey, string $prefixKey, string $defaultPrefix): string
    {
        if (!$this->db->inTransaction()) {
            throw new RuntimeException('NumberService harus dipanggil di dalam transaction.');
        }

        $counterRow = $this->db->fetchOne('SELECT id, value FROM settings WHERE key_name = ? LIMIT 1', [$counterKey]);
        $prefixRow = $this->db->fetchOne('SELECT value FROM settings WHERE key_name = ? LIMIT 1', [$prefixKey]);
        $next = ((int) ($counterRow['value'] ?? 0)) + 1;

        if ($counterRow !== null) {
            $this->db->update('settings', ['value' => (string) $next], 'id = ?', [(int) $counterRow['id']]);
        } else {
            $this->db->insert('settings', ['key_name' => $counterKey, 'value' => (string) $next]);
        }

        return sprintf('%s-%s-%04d', (string) ($prefixRow['value'] ?? $defaultPrefix), date('Y'), $next);
    }
}