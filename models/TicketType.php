<?php

require_once __DIR__ . '/../config/database.php';

class TicketType
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function findByEvento(int $eventoId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ticket_types WHERE evento_id = :evento_id ORDER BY id DESC');
        $stmt->execute(['evento_id' => $eventoId]);
        return $stmt->fetchAll();
    }
}
