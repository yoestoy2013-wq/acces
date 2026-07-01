<?php

require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../models/Invitado.php';

function getServerConnection(): PDO
{
    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['charset']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, $db['user'], $db['password'], $options);
}

function createTestDatabase(PDO $pdo): void
{
    $pdo->exec('DROP DATABASE IF EXISTS access_test');
    $pdo->exec('CREATE DATABASE access_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE access_test');

    $schema = file_get_contents(__DIR__ . '/../database/migrations/001_create_schema.sql');
    $schema = str_replace(
        [
            'CREATE DATABASE IF NOT EXISTS access CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'USE access;',
        ],
        [
            'CREATE DATABASE IF NOT EXISTS access_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'USE access_test;',
        ],
        $schema
    );

    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
    }
}

function runTest(): void
{
    echo "Running invitados CRUD test...\n";

    $pdo = getServerConnection();
    createTestDatabase($pdo);

    $eventoModel = new Evento();
    $ticketTypeModel = new TicketType();
    $invitadoModel = new Invitado();

    echo "Creating event...\n";
    $eventoId = $eventoModel->create([
        'nombre' => 'Test Event for Guests',
        'fecha' => '2026-07-01',
        'lugar' => 'Test Venue',
    ]);
    if ($eventoId <= 0) {
        throw new RuntimeException('Failed to create event.');
    }

    echo "Creating ticket type...\n";
    $ticketTypeId = $ticketTypeModel->create([
        'evento_id' => $eventoId,
        'nombre' => 'General',
        'precio' => 150.00,
        'color' => 'blue',
        'activo' => 1,
    ]);
    if ($ticketTypeId <= 0) {
        throw new RuntimeException('Failed to create ticket type.');
    }

    echo "Creating invitado...\n";
    $invitadoId = $invitadoModel->create([
        'evento_id' => $eventoId,
        'ticket_type_id' => $ticketTypeId,
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'dni' => '12345678',
        'email' => 'juan@example.com',
        'telefono' => '555-1234',
        'observaciones' => 'Test guest',
    ]);
    if ($invitadoId <= 0) {
        throw new RuntimeException('Failed to create invitado.');
    }

    echo "Finding invitado...\n";
    $invitado = $invitadoModel->find($invitadoId);
    if ($invitado === null || $invitado['nombre'] !== 'Juan') {
        throw new RuntimeException('Created invitado not found or incorrect.');
    }

    echo "Listing invitados by evento...\n";
    $list = $invitadoModel->findByEvento($eventoId);
    if (count($list) !== 1 || $list[0]['id'] != $invitadoId) {
        throw new RuntimeException('Invitado listing failed.');
    }

    echo "Updating invitado...\n";
    $updated = $invitadoModel->update($invitadoId, [
        'ticket_type_id' => $ticketTypeId,
        'nombre' => 'Juanito',
        'apellido' => 'Pérez',
        'dni' => '12345678',
        'email' => 'juanito@example.com',
        'telefono' => '555-5678',
        'observaciones' => 'Updated guest',
    ]);
    if (!$updated) {
        throw new RuntimeException('Failed to update invitado.');
    }

    $invitado = $invitadoModel->find($invitadoId);
    if ($invitado === null || $invitado['nombre'] !== 'Juanito' || $invitado['email'] !== 'juanito@example.com') {
        throw new RuntimeException('Invitado update verification failed.');
    }

    echo "Deleting invitado...\n";
    $deleted = $invitadoModel->delete($invitadoId);
    if (!$deleted) {
        throw new RuntimeException('Failed to delete invitado.');
    }

    $invitado = $invitadoModel->find($invitadoId);
    if ($invitado !== null) {
        throw new RuntimeException('Invitado was not deleted.');
    }

    echo "Testing foreign key integrity...\n";
    try {
        $invitadoModel->create([
            'evento_id' => $eventoId,
            'ticket_type_id' => $ticketTypeId + 999,
            'nombre' => 'Invalid',
            'apellido' => '',
            'dni' => '',
            'email' => '',
            'telefono' => '',
            'observaciones' => '',
        ]);
        throw new RuntimeException('Should not allow invitado with invalid ticket type.');
    } catch (PDOException $e) {
        if ($e->getCode() !== '23000') {
            throw new RuntimeException('Unexpected error code for foreign key violation: ' . $e->getCode());
        }
    }

    echo "Invitados CRUD test passed successfully.\n";
}

try {
    runTest();
    echo "Invitados automatic test completed successfully.\n";
} catch (Throwable $e) {
    echo 'Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}
