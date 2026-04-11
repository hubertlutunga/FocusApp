<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = config('database');

        if (!is_array($config)) {
            throw new RuntimeException('Configuration de base de données introuvable.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $exception) {
            throw new RuntimeException('Connexion MySQL impossible : ' . $exception->getMessage());
        }

        return self::$connection;
    }
}
