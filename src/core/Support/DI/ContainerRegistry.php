<?php
declare(strict_types=1);

namespace Core\Support\DI;

use Core\Support\DI\Contracts\ContainerInterface;

final class ContainerRegistry
{
    private static ?ContainerInterface $instance = null;

    public static function get(): ContainerInterface
    {
        if (self::$instance === null) {
            self::$instance = new Container();
        }

        return self::$instance;
    }

    public static function set(ContainerInterface $container): void
    {
        self::$instance = $container;
    }
}

