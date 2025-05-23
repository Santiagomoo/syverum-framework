<?php

namespace Core\Panel;

use Core\Panel\Attributes\Debuggable;
use ReflectionClass;

class Monitoring
{

    protected static array $modules = [];

    // Registrar una clase con un nombre clave
    public static function register(string $name, string $class): void
    {
        self::$modules[$name] = $class;
    }

    // Verifica e inspecciona todos los módulos registrados
    public static function check(): array
    {
        $result = [];

        foreach (self::$modules as $alias => $className) {
            $reflection = new ReflectionClass($className);
            $moduleData = [];

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic()) {
                    $attributes = $property->getAttributes(Debuggable::class);

                    if (!empty($attributes)) {
                        $property->setAccessible(true);
                        $moduleData = $property->getValue();
                    }
                }
            }

            $result[$alias] = $moduleData;
        }

        return $result;
    }
}
