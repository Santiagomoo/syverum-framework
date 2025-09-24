<?php
declare(strict_types=1);

namespace Core\Support\Panel;

use Core\Support\Panel\Attributes\Debuggable;
use ReflectionClass;

class Monitoring
{
    /** @var array<string, class-string> */
    protected static array $modules = [];

    public static function register(string $name, string $class): void
    {
        self::$modules[$name] = $class;
    }

    public static function check(): array
    {
        $result = [];

        foreach (self::$modules as $alias => $className) {
            if (!class_exists($className)) {
                $result[$alias] = [];
                continue;
            }
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
