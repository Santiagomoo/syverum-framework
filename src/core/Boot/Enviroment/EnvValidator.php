<?php

namespace Core\Boot\Enviroment;

class EnvValidator
{

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Indica si el modo debug está activado.
     */
    public static function isDebugEnabled(): bool
    {
        return in_array(strtolower(self::get('APP_DEBUG', 'false')), ['true', '1', 'on'], true);
    }



    /**
     * Devuelve un array con las variables faltantes.
     */
    public static function missing(array $keys): array
    {
        $missing = [];
        foreach ($keys as $key) {
            if (!isset($_ENV[$key]) || trim($_ENV[$key]) === '') {
                $missing[] = $key;
            }
        }
        return $missing;
    }
}
