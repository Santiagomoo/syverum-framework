<?php

namespace Core\Boot\Services;

use Core\Boot\Enviroment\EnvValidator;
use Core\Database\Connection;

class DatabaseService
{
    protected array $config = [];

    public function boot()
    {
        $this->config = [
            'driver'   => EnvValidator::get('DB_CONNECTION'),
            'host'     => EnvValidator::get('DB_HOST'),
            'port'     => EnvValidator::get('DB_PORT'),
            'database' => EnvValidator::get('DB_DATABASE'),
            'username' => EnvValidator::get('DB_USERNAME'),
            'password' => EnvValidator::get('DB_PASSWORD'),
        ];


        // Iniciar la conexión pasando la configuración
        Connection::configure($this->config);
        Connection::getInstance(); // ejecuta la conexión
    }
}