<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

function requireJsonContentType(): void
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== 0) {
        throw new ApiException(415, 'UNSUPPORTED_MEDIA_TYPE', 'Content-Type harus application/json');
    }
}
