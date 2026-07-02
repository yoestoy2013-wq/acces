<?php

require_once __DIR__ . '/../models/TicketType.php';

class TicketTypeController
{
    private TicketType $model;

    public function __construct()
    {
        $this->model = new TicketType();
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre del ticket es requerido.';
        }

        if (empty($data['precio']) || !is_numeric($data['precio']) || (float)$data['precio'] < 0) {
            $errors[] = 'El precio debe ser un número válido mayor o igual a 0.';
        }

        return $errors;
    }

    public function create(int $eventoId, array $data): int
    {
        $data['evento_id'] = $eventoId;
        $id = $this->model->create($data);
        if ($id <= 0) {
            throw new RuntimeException('No se pudo crear el tipo de ticket.');
        }
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function listByEvento(int $eventoId): array
    {
        return $this->model->allByEvento($eventoId);
    }
}
