<?php
declare(strict_types=1);

namespace Kost\Services;

use Kost\Core\Db;
use Kost\Core\HttpException;
use Kost\Middleware\AuthMiddleware;

final class AuthService
{
    public function session(): array
    {
        $user = AuthMiddleware::user();
        if ($user !== null) unset($user['password_hash']);
        return ['user' => $user, 'csrf_token' => AuthMiddleware::csrfToken()];
    }

    public function login(array $body): array
    {
        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            throw new HttpException(422, 'VALIDATION_ERROR', 'Data masuk tidak valid', ['email' => 'Alamat email tidak valid']);
        }
        if (!is_string($password) || $password === '' || strlen($password) > 4096) {
            throw new HttpException(422, 'VALIDATION_ERROR', 'Data masuk tidak valid', ['password' => 'Password wajib diisi']);
        }

        $statement = Db::connection()->prepare(
            'SELECT id,nama,email,password_hash,role,penghuni_id,aktif FROM pengguna WHERE email=:email LIMIT 1'
        );
        $statement->execute(['email' => mb_strtolower($email)]);
        $user = $statement->fetch();
        if (!$user || !(bool)$user['aktif'] || !password_verify($password, $user['password_hash'])) {
            throw new HttpException(401, 'INVALID_CREDENTIALS', 'Email atau password tidak sesuai');
        }

        AuthMiddleware::startSession();
        session_regenerate_id(true);
        $_SESSION = [];
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role'] = $user['role'];
        unset($user['password_hash'], $user['aktif']);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return ['user' => $user, 'csrf_token' => AuthMiddleware::csrfToken()];
    }

    public function logout(): array
    {
        AuthMiddleware::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'],
                'secure' => (bool)$params['secure'],
                'httponly' => (bool)$params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        return ['user' => null];
    }
}
