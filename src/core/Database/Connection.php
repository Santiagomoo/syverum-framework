<?php

namespace Core\Database;

use Core\Panel\Attributes\Debuggable;
use PDO;
use PDOException;

class Connection
{
    protected static ?PDO $instance = null;

    #[Debuggable]
    protected static $database = [];

    public static function configure(array $config): void
    {
        self::$database = array_merge([
            'connected' => false,
            'driver' => $config['driver'] ?? null,
            'database' => $config['database'] ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'error' => null,
        ], $config);

        // Validación para evitar conexión si hay datos faltantes
        if (empty(self::$database['driver']) ||
            empty(self::$database['host']) ||
            empty(self::$database['database']) ||
            empty(self::$database['username'])) {

            self::$database['error'] = 'Faltan datos de conexión. No se intentó conectar.';
        }
    }

    public static function getInstance(): ?PDO
    {
        if (self::$instance === null && self::$database['error'] === null) {
            self::connect();
        }

        return self::$instance;
    }

    protected static function connect(): void
    {
        $driver = self::$database['driver'];

        switch ($driver) {
            case 'mysql':
                self::$instance = self::connectMySQL();
                break;

            default:
                self::$database['error'] = "Driver de base de datos no soportado: {$driver}";
                break;
        }
    }

    protected static function connectMySQL(): ?PDO
    {
        $host = self::$database['host'];
        $port = self::$database['port'];
        $database = self::$database['database'];
        $username = self::$database['username'];
        $password = self::$database['password'];

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            self::$database['connected'] = true;
            self::$database['error'] = null;

            return $pdo;
        } catch (PDOException $e) {
            self::$database['connected'] = false;
            self::$database['error'] = "Error al conectar: " . $e->getMessage();

            return null; // No lanzamos excepción
        }
    }

    public static function getDebugInfo(): array
    {
        return self::$database;
    }
}

?>