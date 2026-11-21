<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Endpoint pembayaran saat ini hanya mendukung GET');
    }
    $user = \Kost\Middleware\RoleMiddleware::require(['admin', 'petugas', 'penghuni']);
    if ($user['role'] === 'penghuni' && empty($user['penghuni_id'])) {
        throw new ApiException(409, 'TENANT_ACCOUNT_UNLINKED', 'Akun belum ditautkan ke data penghuni');
    }
    $rows = (new \Kost\Services\PaymentService(new \Kost\Repositories\PaymentRepository(db())))->listFor($user);
    responseJson(200, ['data' => $rows]);
});
