<?php
declare(strict_types=1);

namespace Kost\Helpers;

use Kost\Core\Http;

final class Response
{
    public static function json(int $status, ?array $payload = null): never
    {
        Http::json($status, $payload);
    }
}
