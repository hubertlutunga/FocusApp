<?php

declare(strict_types=1);

return [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'focuszez_db',
    'username' => 'focuszez_hubert',
    'password' => 'CHANGE_ME',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];