<?php

require_once __DIR__ . '/../config/database.php';

class TicketType
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function allByEvento(int $eventoId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ticket_types WHERE evento_id = :evento_id ORDER BY id DESC');
        $stmt->execute(['evento_id' => $eventoId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ticket_types WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $ticketType = $stmt->fetch();
        return $ticketType ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO ticket_types (evento_id, nombre, precio, color, activo) VALUES (:evento_id, :nombre, :precio, :color, :activo)');
        $stmt->execute([
            'evento_id' => $data['evento_id'],
            'nombre' => $data['nombre'],
            'precio' => $data['precio'],
            'color' => $data['color'],
            'activo' => $data['activo'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE ticket_types SET nombre = :nombre, precio = :precio, color = :color, activo = :activo WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'precio' => $data['precio'],
            'color' => $data['color'],
            'activo' => $data['activo'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ticket_types WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
