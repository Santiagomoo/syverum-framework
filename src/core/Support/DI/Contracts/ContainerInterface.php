<?php
declare(strict_types=1);

namespace Core\Support\DI\Contracts;

interface ContainerInterface
{
    /**
     * Bind an identifier to a concrete implementation.
     *
     * @param string $id Service identifier or class/interface name.
     * @param callable|string|object $concrete Closure, class name, or ready instance.
     * @param bool $shared Whether the binding is a singleton.
     */
    public function bind(string $id, callable|string|object $concrete, bool $shared = false): void;

    /**
     * Bind a ready-made instance as a singleton.
     */
    public function instance(string $id, object $instance): void;

    /**
     * Bind a singlet
     * n service.
     */
    public function singleton(string $id, callable|string|object $concrete): void;

    /**
     * Resolve an entry by its identifier.
     *
     * @template T
     * @param class-string<T>|string $id
     * @param array<string, mixed> $parameters Parameter overrides by name or type.
     * @return T|mixed
     */
    public function make(string $id, array $parameters = []): mixed;

    /**
     * Alias of make to align with typical container APIs.
     *
     * @param string $id
     */
    public function get(string $id): mixed;

    /**
     * Check if a binding exists or the class is instantiable.
     */
    public function has(string $id): bool;

    /**
     * Call a callable/array syntax or "Class::method" resolving parameters from the container.
     *
     * @param callable|array|string $callable
     * @param array<string, mixed> $parameters
     */
    public function call(callable|array|string $callable, array $parameters = []): mixed;
}

