<?php
declare(strict_types=1);

namespace Core\Services\Database;

use PDO;
use PDOStatement;
use RuntimeException;

class Database
{
    public static function pdo(): PDO
    {
        $pdo = Connection::getInstance();

        if (!$pdo) {
            throw new RuntimeException('Database connection not available.');
        }

        return $pdo;
    }

    public static function query(string $sql, array $params = []): array
    {
        return self::select($sql, $params);
    }

    public static function select(string $sql, array $params = []): array
    {
        return self::executeStatement($sql, $params)->fetchAll();
    }

    public static function selectOne(string $sql, array $params = []): ?array
    {
        $result = self::executeStatement($sql, $params)->fetch();

        return $result === false ? null : $result;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        try {
            self::executeStatement($sql, $params);
            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    public static function statement(string $sql, array $params = []): bool
    {
        return self::execute($sql, $params);
    }

    public static function affectingStatement(string $sql, array $params = []): int
    {
        return self::executeStatement($sql, $params)->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::pdo()->lastInsertId();
    }

    private static function executeStatement(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new RuntimeException('Database statement failed to execute.');
        }

        return $stmt;
    }
}