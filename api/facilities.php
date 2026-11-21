<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/http.php';

runApi(function (): void {
    require_once dirname(__DIR__) . '/config/database.php';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        throw new ApiException(405, 'METHOD_NOT_ALLOWED', 'Endpoint fasilitas saat ini hanya mendukung GET');
    }
    responseJson(200, ['data' => (new \Kost\Repositories\FacilityRepository(db()))->all()]);
});
