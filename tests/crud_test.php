<?php

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

function runTest(): void
{
    echo "Running phase 1 CRUD smoke test...\n";

    $pdo = getServerConnection();
    $pdo->exec('CREATE DATABASE IF NOT EXISTS access_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
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

    echo "Database schema loaded for test.\n";

    $pdo->exec("INSERT INTO eventos (nombre, fecha, lugar) VALUES ('Test Event', '2026-07-01', 'Test Venue')");
    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM eventos');
    $row = $stmt->fetch();

    if ((int)$row['total'] !== 1) {
        throw new RuntimeException('Expected one event after insert.');
    }

    echo "CRUD smoke test passed.\n";
}

try {
    runTest();
    echo "Phase 1 automatic test completed successfully.\n";
} catch (Throwable $e) {
    echo 'Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}
