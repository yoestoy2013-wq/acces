<?php

require_once __DIR__ . '/../config/database.php';

class CheckinHistory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    public function create(int $checkinId, ?string $oldStatus, string $newStatus): bool
    {
        $stmt = $this->db->prepare('INSERT INTO checkin_history (checkin_id, estado_anterior, estado_nuevo) VALUES (:checkin_id, :estado_anterior, :estado_nuevo)');
        return $stmt->execute([
            'checkin_id' => $checkinId,
            'estado_anterior' => $oldStatus,
            'estado_nuevo' => $newStatus,
        ]);
    }
}
