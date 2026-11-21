<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/auth.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Endpoint profil hanya mendukung GET');
    }

    // Profil diambil dari akun tersimpan; query AuthMiddleware tidak pernah memilih password_hash.
    $user = requireLogin();
    responseJson(200, ['data' => ['user' => $user]]);
});
