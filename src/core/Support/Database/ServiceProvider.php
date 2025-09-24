<?php
declare(strict_types=1);

namespace Core\Support\Database;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Services\Database\Connection;
use PDO;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(PDO::class, function () {
            $config = [
                'driver' => getenv('DB_DRIVER') ?: 'mysql',
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => getenv('DB_PORT') ?: '3306',
                'database' => getenv('DB_NAME') ?: null,
                'username' => getenv('DB_USER') ?: null,
                'password' => getenv('DB_PASS') ?: null,
            ];

            Connection::configure($config);
            $pdo = Connection::getInstance();
            if (!$pdo) {
                throw new \RuntimeException('Database connection not configured or failed.');
            }
            return $pdo;
        });
    }
}