<?php

require_once __DIR__ . '/../config/database.php';

class Invitado
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function findByEvento(int $eventoId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM invitados WHERE evento_id = :evento_id ORDER BY id DESC');
        $stmt->execute(['evento_id' => $eventoId]);
        return $stmt->fetchAll();
    }

    public function allByEvento(int $eventoId): array
    {
        return $this->findByEvento($eventoId);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM invitados WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $invitado = $stmt->fetch();
        return $invitado ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO invitados (evento_id, ticket_type_id, nombre, apellido, dni, email, telefono, observaciones) VALUES (:evento_id, :ticket_type_id, :nombre, :apellido, :dni, :email, :telefono, :observaciones)');
        $stmt->execute([
            'evento_id' => $data['evento_id'],
            'ticket_type_id' => $data['ticket_type_id'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'observaciones' => $data['observaciones'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE invitados SET ticket_type_id = :ticket_type_id, nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, telefono = :telefono, observaciones = :observaciones WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'ticket_type_id' => $data['ticket_type_id'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'observaciones' => $data['observaciones'],
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM invitados WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
