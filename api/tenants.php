<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/config/auth.php';
    requireRole(['admin', 'petugas']);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Endpoint penghuni saat ini hanya mendukung GET');
    }

    $repository = new \Kost\Repositories\TenantRepository(db());
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) throw new ApiException(400, 'INVALID_ID', 'ID penghuni harus berupa bilangan bulat positif');
        responseJson(200, ['data' => $repository->find($id)]);
    }
    responseJson(200, ['data' => $repository->list()]);
});
