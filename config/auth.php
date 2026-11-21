<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function startAuthSession(): void
{
    \Kost\Middleware\AuthMiddleware::startSession();
}

function currentUser(): ?array
{
    return \Kost\Middleware\AuthMiddleware::user();
}

function requireLogin(): array
{
    return \Kost\Middleware\AuthMiddleware::requireUser();
}

function requireRole(array $allowedRoles): array
{
    return \Kost\Middleware\RoleMiddleware::require($allowedRoles);
}

function csrfToken(): string
{
    return \Kost\Middleware\AuthMiddleware::csrfToken();
}

function requireCsrfToken(): void
{
    \Kost\Middleware\AuthMiddleware::requireCsrf();
}
