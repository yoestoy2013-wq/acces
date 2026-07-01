<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/CheckinHistory.php';

class Checkin
{
    private PDO $db;
    private CheckinHistory $historyModel;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
        $this->historyModel = new CheckinHistory();
    }

    public function searchInvitados(string $query, ?int $eventoId = null): array
    {
        $sql = 'SELECT i.*, t.nombre AS ticket_nombre, t.precio AS ticket_precio, e.nombre AS evento_nombre, c.estado_ingreso AS checkin_estado, c.checkin_time
                FROM invitados i
                JOIN ticket_types t ON i.ticket_type_id = t.id
                JOIN eventos e ON i.evento_id = e.id
                LEFT JOIN (
                    SELECT ci1.*
                    FROM checkins ci1
                    JOIN (
                        SELECT invitado_id, MAX(id) AS max_id
                        FROM checkins
                        GROUP BY invitado_id
                    ) ci2 ON ci1.invitado_id = ci2.invitado_id AND ci1.id = ci2.max_id
                ) c ON c.invitado_id = i.id
                WHERE 1=1';

        $params = [];
        if ($query !== '') {
            $sql .= ' AND (i.nombre LIKE :q1 OR i.apellido LIKE :q2 OR i.dni LIKE :q3)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
        }

        if ($eventoId !== null && $eventoId > 0) {
            $sql .= ' AND e.id = :evento_id';
            $params['evento_id'] = $eventoId;
        }

        $sql .= ' ORDER BY i.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM checkins WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $checkin = $stmt->fetch();
        return $checkin ?: null;
    }

    public function findLatestByInvitado(int $invitadoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM checkins WHERE invitado_id = :invitado_id ORDER BY id DESC LIMIT 1');
        $stmt->execute(['invitado_id' => $invitadoId]);
        $checkin = $stmt->fetch();
        return $checkin ?: null;
    }

    public function create(int $invitadoId, int $ticketTypeId, string $estado, ?string $checkinTime = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO checkins (invitado_id, ticket_type_id, estado_ingreso, checkin_time) VALUES (:invitado_id, :ticket_type_id, :estado_ingreso, :checkin_time)');
        $stmt->execute([
            'invitado_id' => $invitadoId,
            'ticket_type_id' => $ticketTypeId,
            'estado_ingreso' => $estado,
            'checkin_time' => $checkinTime,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateState(int $id, string $estado, ?string $checkinTime = null): bool
    {
        $stmt = $this->db->prepare('UPDATE checkins SET estado_ingreso = :estado_ingreso, checkin_time = :checkin_time, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'estado_ingreso' => $estado,
            'checkin_time' => $checkinTime,
        ]);
    }

    public function register(int $invitadoId, int $ticketTypeId): array
    {
        $latest = $this->findLatestByInvitado($invitadoId);
        $timestamp = date('Y-m-d H:i:s');

        if ($latest && $latest['estado_ingreso'] === 'ingresó') {
            throw new RuntimeException('El invitado ya ingresó.');
        }

        if ($latest) {
            $oldStatus = $latest['estado_ingreso'];
            if (!$this->updateState((int)$latest['id'], 'ingresó', $timestamp)) {
                throw new RuntimeException('No se pudo registrar el ingreso.');
            }
            $this->historyModel->create((int)$latest['id'], $oldStatus, 'ingresó');
            return $this->find((int)$latest['id']);
        }

        $checkinId = $this->create($invitadoId, $ticketTypeId, 'ingresó', $timestamp);
        $this->historyModel->create($checkinId, null, 'ingresó');

        return $this->find($checkinId);
    }
}
