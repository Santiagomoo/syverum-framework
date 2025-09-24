<?php

namespace Core\Services\Database;

use PDO;

class Database
{
    public static function pdo(): PDO
    {
        return Connection::getInstance();
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::pdo()->prepare($sql);
        return $stmt->execute($params);
    }
}

