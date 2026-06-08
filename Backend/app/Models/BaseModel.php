<?php

declare(strict_types=1);

abstract class BaseModel
{
    protected Database $db;
    protected string $table;
    protected string $pk = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE {$this->pk} = ?", [$id]);
    }

    public function findAll(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $direction = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}");
    }

    public function insert(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, "{$this->pk} = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "{$this->pk} = ?", [$id]);
    }

    public function exists(int $id): bool
    {
        return $this->findById($id) !== null;
    }
}