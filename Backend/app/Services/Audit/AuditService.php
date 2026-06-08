<?php

declare(strict_types=1);

final class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(array $params): void
    {
        $this->db->insert('audit_logs', [
            'user_id' => (int) ($params['user_id'] ?? 0),
            'action' => (string) ($params['action'] ?? ''),
            'table_name' => (string) ($params['table_name'] ?? ''),
            'record_id' => (int) ($params['record_id'] ?? 0),
            'old_values' => json_encode($params['old_values'] ?? null, JSON_UNESCAPED_UNICODE),
            'new_values' => json_encode($params['new_values'] ?? null, JSON_UNESCAPED_UNICODE),
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
        ]);
    }
}