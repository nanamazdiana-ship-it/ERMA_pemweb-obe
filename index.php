<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$routes = [
    'public/home' => 'pages/public/home.php',
    'public/units' => 'pages/public/units.php',
    'public/unit-detail' => 'pages/public/unit-detail.php',
    'auth/login' => 'pages/auth/login.php',
    'admin/dashboard' => 'pages/admin/dashboard.php',
    'admin/units' => 'pages/admin/units.php',
    'admin/tenants' => 'pages/admin/tenants.php',
    'admin/payments' => 'pages/admin/payments.php',
    'admin/facilities' => 'pages/admin/facilities.php',
    'admin/complaints' => 'pages/admin/complaints.php',
    'tenant/dashboard' => 'pages/tenant/dashboard.php',
    'tenant/my-room' => 'pages/tenant/my-room.php',
    'tenant/payments' => 'pages/tenant/payments.php',
    'tenant/complaints' => 'pages/tenant/complaints.php',
    'tenant/profile' => 'pages/tenant/profile.php',
];
$page = $_GET['page'] ?? 'public/home';
if (!is_string($page) || !isset($routes[$page])) {
    http_response_code(404);
    $page = 'public/not-found';
    $view = 'pages/public/not-found.php';
} else {
    $view = $routes[$page];
}

$user = null;
$allowedRoles = str_starts_with($page, 'admin/') ? ['admin', 'petugas']
    : (str_starts_with($page, 'tenant/') ? ['penghuni'] : null);
if ($allowedRoles !== null) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/auth.php';
    $user = currentUser();
    if ($user === null) {
        header('Location: index.php?page=auth/login');
        exit;
    }
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        $view = 'pages/public/forbidden.php';
        $page = 'public/error';
    }
} elseif ($page === 'auth/login') {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config/auth.php';
    $user = currentUser();
}

require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/navbar.php';
if (str_starts_with($page, 'admin/')) require __DIR__ . '/layouts/admin-layout.php';
require __DIR__ . '/' . $view;
require __DIR__ . '/layouts/footer.php';
