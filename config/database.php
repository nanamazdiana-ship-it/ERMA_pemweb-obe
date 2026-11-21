<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function db(): PDO
{
    return \Kost\Core\Db::connection();
}

function envRequired(string $key): string
{
    return \Kost\Core\Env::required($key);
}
