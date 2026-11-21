<?php
declare(strict_types=1);

namespace Kost\Middleware;

use Kost\Core\Db;
use Kost\Core\HttpException;

final class AuthMiddleware
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        if (!@session_start([
            'use_strict_mode' => 1,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ])) {
            error_log('[AUTH] Session storage is unavailable.');
            throw new \RuntimeException('Session storage is unavailable.');
        }
    }

    public static function user(): ?array
    {
        self::startSession();
        $userId = $_SESSION['user_id'] ?? null;
        if (!is_int($userId) && !(is_string($userId) && ctype_digit($userId))) return null;

        $statement = Db::connection()->prepare(
            'SELECT id, nama, email, role, penghuni_id, aktif FROM pengguna WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => (int)$userId]);
        $user = $statement->fetch();
        if (!$user || !(bool)$user['aktif']) {
            unset($_SESSION['user_id'], $_SESSION['role']);
            return null;
        }
        unset($user['aktif']);
        $_SESSION['role'] = $user['role'];
        return $user;
    }

    public static function requireUser(): array
    {
        $user = self::user();
        if ($user === null) throw new HttpException(401, 'UNAUTHENTICATED', 'Silakan masuk untuk melanjutkan');
        return $user;
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function requireCsrf(): void
    {
        self::startSession();
        $given = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';
        if (!is_string($given) || !is_string($expected) || $expected === '' || !hash_equals($expected, $given)) {
            throw new HttpException(403, 'CSRF_INVALID', 'Token keamanan tidak valid; muat ulang sesi Anda');
        }
    }
}
