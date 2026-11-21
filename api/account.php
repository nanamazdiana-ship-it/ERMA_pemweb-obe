<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Method tidak diizinkan');
    }

    $user = currentUser();
    if ($user === null) {
        throw new ApiException(401, 'UNAUTHENTICATED', 'Silakan masuk untuk melihat informasi akun');
    }

    if ($user['role'] === 'penghuni' && empty($user['penghuni_id'])) {
        throw new ApiException(409, 'TENANT_ACCOUNT_UNLINKED', 'Akun belum ditautkan ke data penghuni; hubungi petugas kost');
    }

    responseJson(200, ['data' => \Kost\Services\PaymentService::leaseAndPaymentSummary($user)]);
});
