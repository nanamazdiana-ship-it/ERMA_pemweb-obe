<?php
declare(strict_types=1);

namespace Kost\Core;

use JsonException;
use Throwable;

final class Http
{
    public static function json(int $status, ?array $payload = null): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        if ($status !== 204 && $payload !== null) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
        exit;
    }

    public static function body(): array
    {
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
            throw new HttpException(415, 'UNSUPPORTED_MEDIA_TYPE', 'Content-Type harus application/json');
        }
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            throw new HttpException(400, 'INVALID_JSON', 'Body JSON wajib diisi');
        }
        try {
            $data = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpException(400, 'INVALID_JSON', 'Format JSON tidak valid');
        }
        if (!$data instanceof \stdClass) {
            throw new HttpException(400, 'INVALID_BODY', 'Body harus berupa objek JSON');
        }
        return get_object_vars($data);
    }

    public static function run(callable $handler): never
    {
        ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
        try {
            $handler();
        } catch (HttpException $error) {
            self::json($error->status, ['error' => [
                'code' => $error->errorCode,
                'message' => $error->getMessage(),
                'fields' => (object)$error->fields,
            ]]);
        } catch (Throwable $error) {
            error_log(sprintf('[API] %s: %s', $error::class, $error->getMessage()));
            self::json(500, ['error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'Terjadi kesalahan internal. Silakan coba lagi.',
                'fields' => (object)[],
            ]]);
        }
        self::json(500, ['error' => ['code' => 'INTERNAL_ERROR', 'message' => 'Terjadi kesalahan internal.']]);
    }
}
