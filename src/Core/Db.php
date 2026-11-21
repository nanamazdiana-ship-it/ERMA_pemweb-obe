<?php
declare(strict_types=1);

namespace Kost\Core;

use PDO;
use PDOException;

final class Db
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) return self::$connection;
        if (Env::get('SIMULATE_DB_ERROR', 'false') === 'true') {
            throw new PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)");
        }

        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::required('DB_DATABASE');
        $user = Env::required('DB_USERNAME');
        $password = Env::get('DB_PASSWORD', '');
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        self::$connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$connection;
    }
}
