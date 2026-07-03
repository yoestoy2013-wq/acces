<?php

require_once __DIR__ . '/../config/database.php';

class Invitado
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDatabaseConnection();
    }

    /**
     * Generate unique alphanumeric ID for invitado (INV-XXXX-XXXXX)
     */
    private function generateUniqueId(): string
    {
        do {
            $year = date('Y');
            $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
            $seq = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $uniqueId = "INV-{$year}-{$random}{$seq}";
            
            // Verify it's actually unique in the database
            $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM invitados WHERE unique_id = :unique_id');
            $stmt->execute(['unique_id' => $uniqueId]);
            $result = $stmt->fetch();
        } while ($result['count'] > 0);
        
        return $uniqueId;
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
        $uniqueId = $this->generateUniqueId();
        $stmt = $this->db->prepare('INSERT INTO invitados (evento_id, colaborador_id, ticket_type_id, nombre, apellido, dni, email, telefono, observaciones, unique_id) VALUES (:evento_id, :colaborador_id, :ticket_type_id, :nombre, :apellido, :dni, :email, :telefono, :observaciones, :unique_id)');
        $stmt->execute([
            'evento_id' => $data['evento_id'],
            'colaborador_id' => $data['colaborador_id'] ?? null,
            'ticket_type_id' => $data['ticket_type_id'],
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'observaciones' => $data['observaciones'],
            'unique_id' => $uniqueId,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE invitados SET colaborador_id = :colaborador_id, ticket_type_id = :ticket_type_id, nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, telefono = :telefono, observaciones = :observaciones WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'colaborador_id' => $data['colaborador_id'] ?? null,
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

    public function filterByEvento(int $eventoId, string $filterType, string $filterValue): array
    {
        $filterValue = trim($filterValue);
        
        switch ($filterType) {
            case 'nombre':
                $sql = 'SELECT * FROM invitados WHERE evento_id = :evento_id AND nombre LIKE :filter_value ORDER BY id DESC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'evento_id' => $eventoId,
                    'filter_value' => '%' . $filterValue . '%'
                ]);
                break;
                
            case 'ticket':
                $sql = 'SELECT * FROM invitados WHERE evento_id = :evento_id AND ticket_type_id = :filter_value ORDER BY id DESC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'evento_id' => $eventoId,
                    'filter_value' => (int)$filterValue
                ]);
                break;
                
            case 'pendiente':
                $sql = 'SELECT DISTINCT i.* FROM invitados i LEFT JOIN checkins c ON i.id = c.invitado_id WHERE i.evento_id = :evento_id AND (c.id IS NULL OR c.estado_ingreso = "pendiente") ORDER BY i.id DESC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['evento_id' => $eventoId]);
                break;
                
            case 'ingresado':
                $sql = 'SELECT DISTINCT i.* FROM invitados i INNER JOIN checkins c ON i.id = c.invitado_id WHERE i.evento_id = :evento_id AND c.estado_ingreso != "pendiente" ORDER BY i.id DESC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['evento_id' => $eventoId]);
                break;
                
            case 'colaborador':
                $sql = 'SELECT * FROM invitados WHERE evento_id = :evento_id AND colaborador_id = :filter_value ORDER BY id DESC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'evento_id' => $eventoId,
                    'filter_value' => (int)$filterValue
                ]);
                break;
                
            default:
                return $this->findByEvento($eventoId);
        }
        
        return $stmt->fetchAll();
    }
}
