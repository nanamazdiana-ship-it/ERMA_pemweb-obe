<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $id = $_GET['id'] ?? null;

    if ($method === 'GET') {
        $user = \Kost\Middleware\RoleMiddleware::require(['penghuni', 'petugas', 'admin']);
        if ($user['role'] === 'penghuni' && empty($user['penghuni_id'])) {
            throw new ApiException(409, 'TENANT_ACCOUNT_UNLINKED', 'Akun belum ditautkan ke data penghuni');
        }
        $repository = new \Kost\Repositories\ComplaintRepository(db());
        $rows = $user['role'] === 'penghuni'
            ? $repository->listForTenant((int)$user['penghuni_id'])
            : $repository->all();
        responseJson(200, ['data' => $rows]);
    }

    if ($method === 'POST') {
        $user = requireRole(['penghuni']);
        requireCsrfToken();
        if ($id !== null) throw new ApiException(400, 'INVALID_ID', 'POST tidak menerima parameter id');
        $service = new \Kost\Services\ComplaintService(new \Kost\Repositories\ComplaintRepository(db()));
        $created = $service->create($user, readJsonBody());
        responseJson(201, ['data' => $created]);
    }

    if ($method === 'PATCH') {
        requireRole(['petugas', 'admin']);
        requireCsrfToken();
        if ($id === null) throw new ApiException(400, 'INVALID_ID', 'Parameter id wajib diisi');
        $service = new \Kost\Services\ComplaintService(new \Kost\Repositories\ComplaintRepository(db()));
        responseJson(200, ['data' => $service->updateStatus($id, readJsonBody())]);
    }

    header('Allow: GET, POST, PATCH');
    throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Method tidak diizinkan');
});
