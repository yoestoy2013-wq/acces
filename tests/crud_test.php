<?php

putenv('MYSQL_DATABASE=access_test');
putenv('DB_NAME=access_test');
require_once __DIR__ . '/../models/Evento.php';

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

    // Apply migration 001: initial schema
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

    // Apply migration 006: add commercial fields to eventos
    $migration006 = file_get_contents(__DIR__ . '/../database/migrations/006_add_comercial_fields_to_eventos.sql');
    $migration006 = str_replace('USE access;', 'USE access_test;', $migration006);
    
    $statements = array_filter(array_map('trim', explode(';', $migration006)));
    foreach ($statements as $statement) {
        if ($statement === '' || strpos($statement, '--') === 0) {
            continue;
        }
        $pdo->exec($statement);
    }
}

function runTest(): void
{
    echo "Running phase 2 Eventos CRUD test...\n";

    $pdo = getServerConnection();
    createTestDatabase($pdo);

    $eventoModel = new Evento();

    echo "Creating event...\n";
    $id = $eventoModel->create([
        'nombre' => 'Test Event',
        'fecha' => '2026-07-01',
        'lugar' => 'Test Venue',
    ]);
    if ($id <= 0) {
        throw new RuntimeException('Failed to create event.');
    }

    echo "Finding event...\n";
    $created = $eventoModel->find($id);
    if ($created === null || $created['nombre'] !== 'Test Event') {
        throw new RuntimeException('Created event could not be found or has wrong data.');
    }

    echo "Updating event...\n";
    $updated = $eventoModel->update($id, [
        'nombre' => 'Updated Event',
        'fecha' => '2026-07-02',
        'lugar' => 'Updated Venue',
    ]);
    if (!$updated) {
        throw new RuntimeException('Failed to update event.');
    }

    $found = $eventoModel->find($id);
    if ($found === null || $found['nombre'] !== 'Updated Event' || $found['lugar'] !== 'Updated Venue') {
        throw new RuntimeException('Updated event data is incorrect.');
    }

    echo "Searching event...\n";
    $results = $eventoModel->all('Updated');
    if (count($results) !== 1 || $results[0]['id'] != $id) {
        throw new RuntimeException('Search did not return the expected event.');
    }

    echo "Deleting event...\n";
    $deleted = $eventoModel->delete($id);
    if (!$deleted) {
        throw new RuntimeException('Failed to delete event.');
    }

    $deletedEvent = $eventoModel->find($id);
    if ($deletedEvent !== null) {
        throw new RuntimeException('Event was not deleted.');
    }

    echo "Eventos CRUD test passed.\n";
}

try {
    runTest();
    echo "Phase 2 automatic test completed successfully.\n";
} catch (Throwable $e) {
    echo 'Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}
