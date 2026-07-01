<?php

require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../config/database.php';

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
    echo "Running ticket types CRUD test...\n";

    $pdo = getServerConnection();
    createTestDatabase($pdo);

    $eventoModel = new Evento();
    $ticketTypeModel = new TicketType();

    $eventoId = $eventoModel->create([
        'nombre' => 'Test Event for Tickets',
        'fecha' => '2026-07-01',
        'lugar' => 'Test Venue',
    ]);

    echo "Creating ticket type...\n";
    $ticketId = $ticketTypeModel->create([
        'evento_id' => $eventoId,
        'nombre' => 'General',
        'precio' => 150.00,
        'color' => 'blue',
        'activo' => 1,
    ]);

    if ($ticketId <= 0) {
        throw new RuntimeException('Failed to create ticket type.');
    }

    echo "Finding ticket type...\n";
    $ticket = $ticketTypeModel->find($ticketId);
    if ($ticket === null || $ticket['nombre'] !== 'General') {
        throw new RuntimeException('Created ticket type not found or incorrect.');
    }

    echo "Listing ticket types...\n";
    $list = $ticketTypeModel->allByEvento($eventoId);
    if (count($list) !== 1 || $list[0]['id'] != $ticketId) {
        throw new RuntimeException('Ticket type listing failed.');
    }

    echo "Updating ticket type...\n";
    $updated = $ticketTypeModel->update($ticketId, [
        'nombre' => 'VIP',
        'precio' => 300.00,
        'color' => 'gold',
        'activo' => 0,
    ]);
    if (!$updated) {
        throw new RuntimeException('Failed to update ticket type.');
    }

    $ticket = $ticketTypeModel->find($ticketId);
    if ($ticket['nombre'] !== 'VIP' || $ticket['precio'] !== '300.00' || $ticket['activo'] != 0) {
        throw new RuntimeException('Ticket type update verification failed.');
    }

    echo "Deleting ticket type...\n";
    $deleted = $ticketTypeModel->delete($ticketId);
    if (!$deleted) {
        throw new RuntimeException('Failed to delete ticket type.');
    }

    $ticket = $ticketTypeModel->find($ticketId);
    if ($ticket !== null) {
        throw new RuntimeException('Ticket type was not deleted.');
    }

    echo "Testing foreign key integrity...\n";
    try {
        $ticketTypeModel->create([
            'evento_id' => $eventoId + 999,
            'nombre' => 'Invalid',
            'precio' => 100.00,
            'color' => '',
            'activo' => 1,
        ]);
        throw new RuntimeException('Should not allow ticket type with non-existing evento.');
    } catch (PDOException $e) {
        if ($e->getCode() !== '23000') {
            throw new RuntimeException('Unexpected error code for foreign key violation: ' . $e->getCode());
        }
    }

    echo "Ticket types CRUD test passed successfully.\n";
}

try {
    runTest();
    echo "Phase 3 automatic test completed successfully.\n";
} catch (Throwable $e) {
    echo 'Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}
