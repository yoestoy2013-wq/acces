<?php

require_once __DIR__ . '/../models/Invitado.php';
require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../models/Colaborador.php';

class InvitadoController
{
    private Evento $eventoModel;
    private TicketType $ticketTypeModel;
    private Invitado $invitadoModel;
    private Colaborador $colaboradorModel;

    public function __construct()
    {
        $this->eventoModel = new Evento();
        $this->ticketTypeModel = new TicketType();
        $this->invitadoModel = new Invitado();
        $this->colaboradorModel = new Colaborador();
    }

    public function listByEvento(int $eventoId): array
    {
        return $this->invitadoModel->findByEvento($eventoId);
    }

    public function find(int $id): ?array
    {
        return $this->invitadoModel->find($id);
    }

    public function listTicketTypes(int $eventoId): array
    {
        return $this->ticketTypeModel->allByEvento($eventoId);
    }

    public function listColaboradores(): array
    {
        return $this->colaboradorModel->activos();
    }

    public function create(int $eventoId, array $data): int
    {
        if (!$this->eventoModel->find($eventoId)) {
            throw new RuntimeException('Evento no existe.');
        }

        $data['evento_id'] = $eventoId;
        return $this->invitadoModel->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->invitadoModel->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->invitadoModel->delete($id);
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (!isset($data['nombre']) || trim($data['nombre']) === '') {
            $errors[] = 'El nombre no puede quedar vacío.';
        }

        if (!isset($data['ticket_type_id']) || !is_numeric($data['ticket_type_id']) || (int)$data['ticket_type_id'] <= 0) {
            $errors[] = 'Debe seleccionar un tipo de ticket válido.';
        }

        if (!isset($data['colaborador_id']) || !is_numeric($data['colaborador_id']) || (int)$data['colaborador_id'] <= 0) {
            $errors[] = 'Debe seleccionar un colaborador válido.';
        }

        if (isset($data['telefono']) && mb_strlen($data['telefono']) > 50) {
            $errors[] = 'El teléfono no puede tener más de 50 caracteres.';
        }

        if (isset($data['observaciones']) && mb_strlen($data['observaciones']) > 500) {
            $errors[] = 'Las observaciones no pueden tener más de 500 caracteres.';
        }

        return $errors;
    }
}
