<?php

require_once __DIR__ . '/../config/database.php';

class Evento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function all(string $q = null): array
    {
        if ($q === null || $q === '') {
            $stmt = $this->db->query('SELECT * FROM eventos ORDER BY id DESC');
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare('SELECT * FROM eventos WHERE nombre LIKE :q1 OR lugar LIKE :q2 ORDER BY id DESC');
        $stmt->execute([
            'q1' => '%' . $q . '%',
            'q2' => '%' . $q . '%',
        ]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM eventos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $event = $stmt->fetch();
        return $event ?: null;
    }

    public function create(array $data): int
    {
        // Campos opcionales para la versión comercial (preparación para módulo de administración)
        // estado: ENUM('ACTIVO','SUSPENDIDO','FINALIZADO') - será gestionado por el módulo de administración
        // organizador: VARCHAR(150) - nombre de la organización/persona responsable
        // fecha_vencimiento: DATE - fecha límite para operaciones del evento
        $estado = $data['estado'] ?? 'ACTIVO';
        $organizador = $data['organizador'] ?? null;
        $fecha_vencimiento = $data['fecha_vencimiento'] ?? null;

        $stmt = $this->db->prepare(
            'INSERT INTO eventos (nombre, fecha, lugar, estado, organizador, fecha_vencimiento) 
             VALUES (:nombre, :fecha, :lugar, :estado, :organizador, :fecha_vencimiento)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'fecha' => $data['fecha'],
            'lugar' => $data['lugar'],
            'estado' => $estado,
            'organizador' => $organizador,
            'fecha_vencimiento' => $fecha_vencimiento,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        // Campos opcionales para la versión comercial (preparación para módulo de administración)
        // estado: ENUM('ACTIVO','SUSPENDIDO','FINALIZADO') - será gestionado por el módulo de administración
        // organizador: VARCHAR(150) - nombre de la organización/persona responsable
        // fecha_vencimiento: DATE - fecha límite para operaciones del evento
        $estado = $data['estado'] ?? 'ACTIVO';
        $organizador = $data['organizador'] ?? null;
        $fecha_vencimiento = $data['fecha_vencimiento'] ?? null;

        $stmt = $this->db->prepare(
            'UPDATE eventos 
             SET nombre = :nombre, fecha = :fecha, lugar = :lugar, estado = :estado, 
                 organizador = :organizador, fecha_vencimiento = :fecha_vencimiento 
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'fecha' => $data['fecha'],
            'lugar' => $data['lugar'],
            'estado' => $estado,
            'organizador' => $organizador,
            'fecha_vencimiento' => $fecha_vencimiento,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM eventos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
