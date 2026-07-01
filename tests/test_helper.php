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

function createTestDatabase(PDO $pdo): void
{
    $pdo->exec('DROP DATABASE IF EXISTS access_test');
    $pdo->exec('CREATE DATABASE access_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE access_test');

    $migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
    sort($migrationFiles, SORT_NATURAL);

    foreach ($migrationFiles as $file) {
        $schema = file_get_contents($file);
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
}
