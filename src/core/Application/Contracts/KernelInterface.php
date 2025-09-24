<?php
declare(strict_types=1);

namespace Core\Application\Contracts;

use Core\Services\Application;

interface KernelInterface
{
    /**
     * Boot and register the module concerns into the application.
     */
    public function boot( Application $app): void;
}


