<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

$baseUrl = rtrim(getenv('KOST_TEST_BASE_URL') ?: 'http://localhost/pemweb-obe', '/');
$suffix = bin2hex(random_bytes(6));
$password = bin2hex(random_bytes(16)) . 'Aa9!';
$db = db();
$tenantId = null;
$userIds = [];
$unitIds = [];
$checks = 0;
$failed = false;

function verify(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $message);
    $checks++;
    fwrite(STDOUT, "PASS: {$message}\n");
}

function apiRequest(string $path, string $method = 'GET', ?array $body = null, ?string &$cookie = null, string $csrf = ''): array
{
    global $baseUrl;
    $headers = ['Accept: application/json'];
    if ($cookie !== null && $cookie !== '') $headers[] = 'Cookie: ' . $cookie;
    if ($method !== 'GET') $headers[] = 'X-CSRF-Token: ' . $csrf;
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    $options = [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'ignore_errors' => true,
        'timeout' => 10,
    ];
    if ($body !== null) $options['content'] = json_encode($body, JSON_THROW_ON_ERROR);
    $raw = file_get_contents($baseUrl . $path, false, stream_context_create(['http' => $options]));
    $responseHeaders = $http_response_header ?? [];
    foreach ($responseHeaders as $header) {
        if (preg_match('/^Set-Cookie:\s*([^;]+)/i', $header, $match)) $cookie = $match[1];
    }
    preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $match);
    return [(int)($match[1] ?? 0), $raw ? json_decode($raw, true) : null];
}

function loginAs(string $role, string $email, string $password): array
{
    $cookie = '';
    [$status, $session] = apiRequest('/api/auth.php', 'GET', null, $cookie);
    if ($status !== 200) throw new RuntimeException("Gagal memulai sesi uji {$role}.");
    $oldCookie = $cookie;
    $csrf = $session['data']['csrf_token'] ?? '';
    [$status, $result] = apiRequest('/api/auth.php', 'POST', [
        'action' => 'login', 'email' => $email, 'password' => $password,
    ], $cookie, $csrf);
    verify($status === 200 && ($result['data']['user']['role'] ?? null) === $role, "login role {$role}");
    verify($oldCookie !== '' && $cookie !== $oldCookie, "session ID diregenerasi saat login {$role}");
    return [$cookie, $result['data']['csrf_token'] ?? ''];
}

try {
    $db->beginTransaction();
    $tenant = $db->prepare('INSERT INTO penghuni_kost (nama_lengkap,no_hp,alamat) VALUES (:nama,:hp,:alamat)');
    $tenant->execute(['nama' => 'Uji Auth ' . $suffix, 'hp' => '080000000000', 'alamat' => 'Data sementara pengujian']);
    $tenantId = (int)$db->lastInsertId();
    $insertUser = $db->prepare(
        'INSERT INTO pengguna (nama,email,password_hash,role,penghuni_id) VALUES (:nama,:email,:hash,:role,:tenant)'
    );
    foreach ([
        'penghuni' => [$suffix . '-viewer@example.invalid', $tenantId],
        'petugas' => [$suffix . '-staff@example.invalid', null],
        'admin' => [$suffix . '-admin@example.invalid', null],
    ] as $role => [$email, $linkedTenant]) {
        $insertUser->execute([
            'nama' => 'Uji ' . ucfirst($role),
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'tenant' => $linkedTenant,
        ]);
        $userIds[$role] = (int)$db->lastInsertId();
    }
    $db->commit();

    $anonymousCookie = '';
    [$status] = apiRequest('/api/units.php', 'GET', null, $anonymousCookie);
    verify($status === 200, 'anonymous boleh membaca unit');
    [$status, $anonymousProfile] = apiRequest('/api/me.php', 'GET', null, $anonymousCookie);
    verify($status === 401 && ($anonymousProfile['error']['code'] ?? '') === 'UNAUTHENTICATED', 'anonymous ditolak dari profil dengan 401');
    [$status, $anonymousWrite] = apiRequest('/api/units.php', 'POST', ['kode' => 'NO-AUTH'], $anonymousCookie);
    verify($status === 401 && ($anonymousWrite['error']['code'] ?? '') === 'UNAUTHENTICATED', 'anonymous ditolak saat membuat unit dengan 401');

    [$viewerCookie, $viewerCsrf] = loginAs('penghuni', $suffix . '-viewer@example.invalid', $password);
    [$status, $viewerProfile] = apiRequest('/api/me.php', 'GET', null, $viewerCookie);
    verify($status === 200 && ($viewerProfile['data']['user']['role'] ?? '') === 'penghuni', 'penghuni membaca profil sendiri');
    verify(!array_key_exists('password_hash', $viewerProfile['data']['user'] ?? []), 'profil tidak memuat password hash');
    [$status] = apiRequest('/api/units.php', 'GET', null, $viewerCookie);
    verify($status === 200, 'penghuni boleh membaca unit');
    [$status] = apiRequest('/api/units.php', 'POST', ['kode' => 'NO-VIEW'], $viewerCookie, $viewerCsrf);
    verify($status === 403, 'penghuni yang sudah login ditolak saat mengubah unit dengan 403');

    [$staffCookie, $staffCsrf] = loginAs('petugas', $suffix . '-staff@example.invalid', $password);
    [$status, $staffProfile] = apiRequest('/api/me.php', 'GET', null, $staffCookie);
    verify($status === 200 && ($staffProfile['data']['user']['role'] ?? '') === 'petugas', 'petugas membaca profil sendiri');
    $staffCode = 'TS-' . strtoupper(substr($suffix, 0, 8));
    [$status, $staffUnit] = apiRequest('/api/units.php', 'POST', [
        'kode' => $staffCode, 'nama' => 'Unit Uji Petugas', 'status' => 'Tersedia', 'harga' => 1,
    ], $staffCookie, $staffCsrf);
    verify($status === 201 && isset($staffUnit['data']['id']), 'petugas boleh membuat unit');
    if (isset($staffUnit['data']['id'])) $unitIds[] = (int)$staffUnit['data']['id'];
    $staffUnitId = (int)($staffUnit['data']['id'] ?? 0);
    [$status] = apiRequest('/api/units.php?id=' . $staffUnitId, 'PATCH', ['status' => 'Terisi'], $staffCookie, $staffCsrf);
    verify($status === 200, 'petugas boleh mengubah unit');
    [$status] = apiRequest('/api/units.php?id=' . end($unitIds), 'DELETE', null, $staffCookie, $staffCsrf);
    verify($status === 403, 'petugas ditolak saat menghapus unit');

    [$adminCookie, $adminCsrf] = loginAs('admin', $suffix . '-admin@example.invalid', $password);
    [$status, $adminProfile] = apiRequest('/api/me.php', 'GET', null, $adminCookie);
    verify($status === 200 && ($adminProfile['data']['user']['role'] ?? '') === 'admin', 'admin membaca profil sendiri');
    [$status, $adminUnit] = apiRequest('/api/units.php', 'POST', [
        'kode' => 'TA-' . strtoupper(substr($suffix, 0, 8)), 'nama' => 'Unit Uji Admin', 'status' => 'Tersedia', 'harga' => 1,
    ], $adminCookie, $adminCsrf);
    verify($status === 201 && isset($adminUnit['data']['id']), 'admin boleh membuat unit');
    if (isset($adminUnit['data']['id'])) $unitIds[] = (int)$adminUnit['data']['id'];
    $adminUnitId = (int)($adminUnit['data']['id'] ?? 0);
    [$status] = apiRequest('/api/units.php?id=' . $adminUnitId, 'PATCH', ['status' => 'Terisi'], $adminCookie, $adminCsrf);
    verify($status === 200, 'admin boleh mengubah unit');

    [$status] = apiRequest('/api/units.php?id=' . $adminUnitId, 'DELETE', null, $adminCookie, $adminCsrf);
    verify($status === 204, 'admin boleh menghapus unit');
    $unitIds = array_values(array_diff($unitIds, [$adminUnitId]));
    foreach ($unitIds as $unitId) apiRequest('/api/units.php?id=' . $unitId, 'DELETE', null, $adminCookie, $adminCsrf);
    $unitIds = [];

    foreach ([['penghuni', $viewerCookie, $viewerCsrf], ['petugas', $staffCookie, $staffCsrf], ['admin', $adminCookie, $adminCsrf]] as [$role, $cookie, $csrf]) {
        [$status, $logout] = apiRequest('/api/auth.php', 'POST', ['action' => 'logout'], $cookie, $csrf);
        verify($status === 200 && array_key_exists('user', $logout['data'] ?? []) && $logout['data']['user'] === null, "logout role {$role}");
        [$status, $session] = apiRequest('/api/auth.php', 'GET', null, $cookie);
        verify($status === 200 && array_key_exists('user', $session['data'] ?? []) && $session['data']['user'] === null, "sesi role {$role} berakhir");
    }

    fwrite(STDOUT, "{$checks} pemeriksaan otorisasi lulus.\n");
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    $failed = true;
} finally {
    if ($db->inTransaction()) $db->rollBack();
    if ($unitIds) {
        $deleteUnits = $db->prepare('DELETE FROM unit_kost WHERE id = ?');
        foreach ($unitIds as $id) $deleteUnits->execute([$id]);
    }
    if ($userIds) {
        $deleteUsers = $db->prepare('DELETE FROM pengguna WHERE id = ?');
        foreach ($userIds as $id) $deleteUsers->execute([$id]);
    }
    if ($tenantId !== null) {
        $deleteTenant = $db->prepare('DELETE FROM penghuni_kost WHERE id = ?');
        $deleteTenant->execute([$tenantId]);
    }
}
if ($failed) exit(1);
