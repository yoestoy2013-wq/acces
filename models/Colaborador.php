<?php

require_once __DIR__ . '/../config/database.php';

class Colaborador
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM colaboradores WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $colaborador = $stmt->fetch();
        return $colaborador ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM colaboradores ORDER BY nombre ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function activos(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM colaboradores WHERE activo = 1 ORDER BY nombre ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO colaboradores (nombre, telefono, observaciones, activo) VALUES (:nombre, :telefono, :observaciones, :activo)');
        $stmt->execute([
            'nombre' => $data['nombre'],
            'telefono' => $data['telefono'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => isset($data['activo']) ? (int)$data['activo'] : 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE colaboradores SET nombre = :nombre, telefono = :telefono, observaciones = :observaciones, activo = :activo WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'telefono' => $data['telefono'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => isset($data['activo']) ? (int)$data['activo'] : 1,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM colaboradores WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function hasInvitados(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM invitados WHERE colaborador_id = :colaborador_id');
        $stmt->execute(['colaborador_id' => $id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function countInvitados(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM invitados WHERE colaborador_id = :colaborador_id');
        $stmt->execute(['colaborador_id' => $id]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
