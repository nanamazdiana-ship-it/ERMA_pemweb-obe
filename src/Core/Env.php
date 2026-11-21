<?php
declare(strict_types=1);

namespace Kost\Core;

final class Env
{
    private static bool $loaded = false;

    public static function load(string $root): void
    {
        if (self::$loaded) return;
        \Dotenv\Dotenv::createImmutable($root)->safeLoad();
        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $value === false || $value === null ? $default : (string)$value;
    }

    public static function required(string $key): string
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            throw new \RuntimeException("Konfigurasi environment {$key} belum diisi.");
        }
        return $value;
    }
}
