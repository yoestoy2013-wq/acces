<?php
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '3306',
        'dbname' => getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'access',
        'user' => getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root',
        'password' => getenv('MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: 'root',
        'charset' => 'utf8mb4',
    ],
];
