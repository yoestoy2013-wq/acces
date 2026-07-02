<?php

require_once __DIR__ . '/../models/Colaborador.php';

class ColaboradorController
{
    private Colaborador $model;

    public function __construct()
    {
        $this->model = new Colaborador();
    }

    public function validateData(array $data): array
    {
        $errors = [];

        if (!isset($data['nombre']) || trim($data['nombre']) === '') {
            $errors[] = 'El nombre del colaborador es requerido.';
        }

        if (isset($data['nombre']) && mb_strlen($data['nombre']) > 150) {
            $errors[] = 'El nombre no puede tener más de 150 caracteres.';
        }

        if (isset($data['telefono']) && mb_strlen($data['telefono']) > 50) {
            $errors[] = 'El teléfono no puede tener más de 50 caracteres.';
        }

        if (isset($data['observaciones']) && mb_strlen($data['observaciones']) > 500) {
            $errors[] = 'Las observaciones no pueden tener más de 500 caracteres.';
        }

        return $errors;
    }

    public function create(array $data): int
    {
        $id = $this->model->create($data);
        if ($id <= 0) {
            throw new RuntimeException('No se pudo crear el colaborador.');
        }
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        if ($this->model->hasInvitados($id)) {
            throw new RuntimeException('No se puede eliminar este colaborador porque tiene invitados asociados.');
        }
        return $this->model->delete($id);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function list(): array
    {
        return $this->model->all();
    }

    public function listActivos(): array
    {
        return $this->model->activos();
    }

    public function countInvitados(int $id): int
    {
        return $this->model->countInvitados($id);
    }
}
