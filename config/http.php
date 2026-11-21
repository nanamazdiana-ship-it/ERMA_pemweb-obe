<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

class ApiException extends \Kost\Core\HttpException
{
}

function responseJson(int $status, ?array $payload = null): never
{
    \Kost\Core\Http::json($status, $payload);
}

function readJsonBody(): array
{
    return \Kost\Core\Http::body();
}

function runApi(callable $handler): never
{
    \Kost\Core\Http::run($handler);
}
