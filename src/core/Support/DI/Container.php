<?php
declare(strict_types=1);

namespace Core\Support\DI;

use Closure;
use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Exceptions\ContainerException;
use Core\Support\DI\Exceptions\NotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class Container implements ContainerInterface
{
    /** @var array<string, array{concrete: callable|string|object, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<int, string> */
    private array $resolvingStack = [];

    public function bind(string $id, callable|string|object $concrete, bool $shared = false): void
    {
        if (is_object($concrete) && !$concrete instanceof Closure) {
            $this->instances[$id] = $concrete;
            $this->bindings[$id] = ['concrete' => $concrete, 'shared' => true];
            return;
        }

        $this->bindings[$id] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        $this->bindings[$id] = ['concrete' => $instance, 'shared' => true];
    }

    public function singleton(string $id, callable|string|object $concrete): void
    {
        $this->bind($id, $concrete, true);
    }

    public function make(string $id, array $parameters = []): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (in_array($id, $this->resolvingStack, true)) {
            throw new ContainerException('Circular dependency detected while resolving: ' . $id);
        }

        $this->resolvingStack[] = $id;
        try {
            $object = $this->resolve($id, $parameters);
        } finally {
            array_pop($this->resolvingStack);
        }

        if ($this->isShared($id)) {
            $this->instances[$id] = is_object($object) ? $object : (object) $object;
            // Store the exact object only if it is an object
            if (is_object($object)) {
                $this->instances[$id] = $object;
            }
        }

        return $object;
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    public function has(string $id): bool
    {
        if (isset($this->bindings[$id]) || isset($this->instances[$id])) {
            return true;
        }

        // If it's a class/interface that can be autowired
        try {
            $ref = new ReflectionClass($id);
            return $ref->isInstantiable();
        } catch (ReflectionException) {
            return false;
        }
    }

    public function call(callable|array|string $callable, array $parameters = []): mixed
    {
        // Normalize callable: 'Class::method' => [instance, method]
        if (is_string($callable) && str_contains($callable, '::')) {
            [$class, $method] = explode('::', $callable, 2);
            $callable = [$this->make($class), $method];
        }

        if (is_array($callable)) {
            [$target, $method] = $callable;
            if (is_string($target)) {
                $target = $this->make($target);
            }
            $reflection = new ReflectionMethod($target, (string) $method);
            $args = $this->resolveFunctionArgs($reflection->getParameters(), $parameters);
            return $reflection->invokeArgs($target, $args);
        }

        if ($callable instanceof Closure) {
            $rf = new ReflectionFunction($callable);
            $args = $this->resolveFunctionArgs($rf->getParameters(), $parameters);
            return $callable(...$args);
        }

        // callable string function name or invokable object
        return $callable(...$parameters);
    }

    private function resolve(string $id, array $parameters): mixed
    {
        // Explicit binding
        if (isset($this->bindings[$id])) {
            $entry = $this->bindings[$id];
            $concrete = $entry['concrete'];

            if ($concrete instanceof Closure) {
                return $concrete($this, $parameters);
            }

            if (is_string($concrete)) {
                return $this->build($concrete, $parameters);
            }

            if (is_object($concrete)) {
                return $concrete; // already instance
            }
        }

        // Autowire if class exists
        if (class_exists($id)) {
            return $this->build($id, $parameters);
        }

        throw new NotFoundException('No entry found or resolvable for identifier: ' . $id);
    }

    /**
     * @param class-string $class
     */
    private function build(string $class, array $parameters = []): object
    {
        try {
            $ref = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new NotFoundException('Class not found: ' . $class, previous: $e);
        }

        if (!$ref->isInstantiable()) {
            throw new ContainerException('Class is not instantiable: ' . $class);
        }

        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            return new $class();
        }

        $args = $this->resolveFunctionArgs($ctor->getParameters(), $parameters);
        return $ref->newInstanceArgs($args);
    }

    /**
        * @param ReflectionParameter[] $params
        * @param array<string, mixed> $overrides
        * @return array<int, mixed>
        */
    private function resolveFunctionArgs(array $params, array $overrides): array
    {
        $args = [];
        foreach ($params as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $overrides)) {
                $args[] = $overrides[$name];
                continue;
            }

            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $depId = $type->getName();

                // Allow injecting the container itself when asked for the interface
                if ($depId === ContainerInterface::class) {
                    $args[] = $this;
                    continue;
                }

                if (array_key_exists($depId, $overrides)) {
                    $args[] = $overrides[$depId];
                    continue;
                }

                // Respect default null on nullable-typed params
                if ($type->allowsNull() && $param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }

                $args[] = $this->make($depId);
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            if ($param->allowsNull()) {
                $args[] = null;
                continue;
            }

            throw new ContainerException('Unable to resolve parameter $' . $name . ' for callable.');
        }

        return $args;
    }

    private function isShared(string $id): bool
    {
        return isset($this->bindings[$id]) && $this->bindings[$id]['shared'] === true;
    }
}
