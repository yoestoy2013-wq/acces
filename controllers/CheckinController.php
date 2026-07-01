<?php

require_once __DIR__ . '/../models/Checkin.php';
require_once __DIR__ . '/../models/Invitado.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../models/Evento.php';

class CheckinController
{
    private Evento $eventoModel;
    private Invitado $invitadoModel;
    private TicketType $ticketTypeModel;
    private Checkin $checkinModel;

    public function __construct()
    {
        $this->eventoModel = new Evento();
        $this->invitadoModel = new Invitado();
        $this->ticketTypeModel = new TicketType();
        $this->checkinModel = new Checkin();
    }

    public function findEvento(int $id): ?array
    {
        return $this->eventoModel->find($id);
    }

    public function searchInvitados(string $query, ?int $eventoId = null): array
    {
        if ($query === '' && (!$eventoId || $eventoId <= 0)) {
            return [];
        }

        return $this->checkinModel->searchInvitados($query, $eventoId);
    }

    public function registerCheckin(int $invitadoId): array
    {
        $invitado = $this->invitadoModel->find($invitadoId);
        if (!$invitado) {
            throw new RuntimeException('Invitado no existe.');
        }

        return $this->checkinModel->register($invitadoId, (int)$invitado['ticket_type_id']);
    }
}
