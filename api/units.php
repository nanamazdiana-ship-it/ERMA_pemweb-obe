<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $hasId = array_key_exists('id', $_GET);

    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) requireRole(['petugas', 'admin']);
    elseif ($method === 'DELETE') requireRole(['admin']);
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) requireCsrfToken();

    $service = new \Kost\Services\UnitService(new \Kost\Repositories\UnitRepository(db()));
    switch ($method) {
        case 'GET':
            if ($hasId) responseJson(200, ['data' => $service->find($_GET['id'])]);
            responseJson(200, $service->list($_GET));
        case 'POST':
            if ($hasId) throw new ApiException(400, 'INVALID_ID', 'POST tidak menerima parameter id');
            $unit = $service->create(readJsonBody());
            http_response_code(201);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/api/units.php', '?') . '?id=' . $unit['id']);
            responseJson(201, ['data' => $unit]);
        case 'PUT':
        case 'PATCH':
            if (!$hasId) throw new ApiException(400, 'INVALID_ID', 'Parameter id wajib diisi');
            responseJson(200, ['data' => $service->update($_GET['id'], readJsonBody(), $method === 'PATCH')]);
        case 'DELETE':
            if (!$hasId) throw new ApiException(400, 'INVALID_ID', 'Parameter id wajib diisi');
            $service->delete($_GET['id']);
            responseJson(204);
        default:
            header('Allow: GET, POST, PUT, PATCH, DELETE');
            throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Method tidak diizinkan');
    }
});
