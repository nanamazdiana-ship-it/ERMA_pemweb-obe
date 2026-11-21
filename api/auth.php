<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';
    $service = new \Kost\Services\AuthService();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') responseJson(200, ['data' => $service->session()]);
    if ($method !== 'POST') {
        header('Allow: GET, POST');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Method tidak diizinkan');
    }

    requireCsrfToken();
    $body = readJsonBody();
    $action = $body['action'] ?? 'login';
    if (!is_string($action) || !in_array($action, ['login', 'logout'], true)) {
        throw new ApiException(400, 'INVALID_ACTION', 'Aksi autentikasi tidak valid');
    }
    $data = $action === 'logout' ? $service->logout() : $service->login($body);
    responseJson(200, ['data' => $data]);
});
