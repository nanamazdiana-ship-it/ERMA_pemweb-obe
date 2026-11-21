<?php
declare(strict_types=1);

namespace Kost\Middleware;

use Kost\Core\HttpException;

final class RoleMiddleware
{
    public static function require(array $allowedRoles): array
    {
        $user = AuthMiddleware::requireUser();
        if (!in_array($user['role'] ?? null, $allowedRoles, true)) {
            throw new HttpException(403, 'FORBIDDEN', 'Anda tidak memiliki izin untuk tindakan ini');
        }
        return $user;
    }
}
