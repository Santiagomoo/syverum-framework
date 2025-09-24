<?php
declare(strict_types=1);

namespace Core\Support\DI;

use Core\Support\DI\Contracts\ContainerInterface;

function container(?ContainerInterface $set = null): ContainerInterface
{
    if ($set !== null) {
        ContainerRegistry::set($set);
    }

    return ContainerRegistry::get();
}

function bind(string $id, callable|string|object $concrete, bool $shared = false): void
{
    container()->bind($id, $concrete, $shared);
}

function singleton(string $id, callable|string|object $concrete): void
{
    container()->singleton($id, $concrete);
}

function instance(string $id, object $object): void
{
    container()->instance($id, $object);
}

function make(string $id, array $parameters = []): mixed
{
    return container()->make($id, $parameters);
}

function call(callable|array|string $callable, array $parameters = []): mixed
{
    return container()->call($callable, $parameters);
}
