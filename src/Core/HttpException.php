<?php
declare(strict_types=1);

namespace Kost\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $errorCode,
        string $message,
        public readonly array $fields = []
    ) {
        parent::__construct($message);
    }
}
