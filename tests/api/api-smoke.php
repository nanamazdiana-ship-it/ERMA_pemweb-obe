<?php
declare(strict_types=1);

$baseUrl = rtrim(getenv('KOST_TEST_BASE_URL') ?: 'http://localhost/pemweb-obe', '/');
$email = getenv('KOST_TEST_EMAIL') ?: '';
$password = getenv('KOST_TEST_PASSWORD') ?: '';
if ($email === '' || $password === '') {
    fwrite(STDERR, "Set KOST_TEST_EMAIL dan KOST_TEST_PASSWORD untuk akun admin pengujian.\n");
    exit(2);
}

$cookie = '';
$csrf = '';
$checks = 0;

function requestApi(string $path, string $method = 'GET', ?array $body = null, bool $withSession = true): array
{
    global $baseUrl, $cookie, $csrf;
    $headers = ['Accept: application/json'];
    if ($withSession && $cookie !== '') $headers[] = 'Cookie: ' . $cookie;
    if ($body !== null) $headers[] = 'Content-Type: application/json';
    if ($method !== 'GET') $headers[] = 'X-CSRF-Token: ' . $csrf;
    $httpOptions = [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'ignore_errors' => true,
        'timeout' => 10,
    ];
    if ($body !== null) $httpOptions['content'] = json_encode($body, JSON_THROW_ON_ERROR);
    $context = stream_context_create(['http' => $httpOptions]);
    $raw = file_get_contents($baseUrl . $path, false, $context);
    $responseHeaders = $http_response_header ?? [];
    foreach ($responseHeaders as $header) {
        if ($withSession && preg_match('/^Set-Cookie:\s*([^;]+)/i', $header, $matches)) $cookie = $matches[1];
    }
    preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $matches);
    return [(int)($matches[1] ?? 0), $raw === false || $raw === '' ? null : json_decode($raw, true)];
}

function check(bool $condition, string $description): void
{
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $description);
    $checks++;
    fwrite(STDOUT, "PASS: {$description}\n");
}

$testCode = 'TEST-' . strtoupper(bin2hex(random_bytes(4)));
$testId = null;
$failed = false;
try {
    [$status, $session] = requestApi('/api/auth.php');
    check($status === 200 && isset($session['data']['csrf_token']), 'status sesi dan token CSRF (HTTP ' . $status . ', ' . ($session['error']['code'] ?? 'no-code') . ')');
    check($cookie !== '', 'cookie sesi diterbitkan');
    $csrf = $session['data']['csrf_token'];
    [$status, $sessionCheck] = requestApi('/api/auth.php');
    $nextToken = $sessionCheck['data']['csrf_token'] ?? '';
    check($status === 200 && hash_equals($csrf, $nextToken), 'session dan CSRF bertahan antar-request (HTTP ' . $status . ', token ' . strlen($nextToken) . ' karakter)');
    [$status, $wrongLogin] = requestApi('/api/auth.php', 'POST', ['action' => 'login', 'email' => $email, 'password' => 'password-salah']);
    check($status === 401, 'login dengan password salah (HTTP ' . $status . ', ' . ($wrongLogin['error']['code'] ?? 'no-code') . ')');
    [$status, $login] = requestApi('/api/auth.php', 'POST', ['action' => 'login', 'email' => $email, 'password' => $password]);
    check($status === 200 && ($login['data']['user']['role'] ?? '') === 'admin', 'login admin');
    $csrf = $login['data']['csrf_token'];
    [$status] = requestApi('/api/units.php', 'POST', ['kode' => 'NO-AUTH'], false);
    check($status === 401, 'write ditolak tanpa login');
    $validCsrf = $csrf;
    $csrf = 'token-salah';
    [$status] = requestApi('/api/units.php', 'POST', ['kode' => 'NO-CSRF']);
    check($status === 403, 'write ditolak tanpa token CSRF valid');
    $csrf = $validCsrf;
    [$status, $inventory] = requestApi('/api/facilities.php');
    check($status === 200 && count($inventory['data'] ?? []) >= 5, 'inventaris dibaca dari database');
    [$status, $account] = requestApi('/api/account.php');
    check($status === 200 && isset($account['data']['leases'], $account['data']['payments']), 'endpoint akun hanya mengembalikan koleksi yang diizinkan');

    [$status, $created] = requestApi('/api/units.php', 'POST', [
        'kode' => $testCode, 'nama' => 'Unit test', 'status' => 'Tersedia', 'harga' => 1000000, 'fasilitas' => 'Air, listrik'
    ]);
    check($status === 201 && isset($created['data']['id']), 'POST unit valid');
    $testId = (int)$created['data']['id'];
    [$status] = requestApi('/api/units.php', 'POST', [
        'kode' => $testCode, 'nama' => 'Unit duplikat', 'status' => 'Tersedia', 'harga' => 1
    ]);
    check($status === 409, 'kode duplikat ditolak');

    [$status, $list] = requestApi('/api/units.php?q=' . rawurlencode($testCode));
    check($status === 200 && count($list['data'] ?? []) === 1, 'GET koleksi dan pencarian');
    [$status, $detail] = requestApi('/api/units.php?id=' . $testId);
    check($status === 200 && $detail['data']['kode'] === $testCode, 'GET detail');
    [$status] = requestApi('/api/units.php', 'POST', ['kode' => '', 'nama' => '', 'status' => 'invalid', 'harga' => -1]);
    check($status === 422, 'POST invalid ditolak');
    [$status] = requestApi('/api/units.php?id=' . $testId, 'PATCH', ['status' => 'Terisi']);
    check($status === 200, 'PATCH valid');
    [$status] = requestApi('/api/units.php?id=999999999', 'GET');
    check($status === 404, 'GET ID tidak ditemukan');
    [$status] = requestApi('/api/units.php?id=' . $testId, 'DELETE');
    check($status === 204, 'DELETE unit test (HTTP ' . $status . ')');
    $testId = null;
    [$status, $logout] = requestApi('/api/auth.php', 'POST', ['action' => 'logout']);
    check($status === 200 && array_key_exists('user', $logout['data'] ?? []) && $logout['data']['user'] === null, 'logout mengakhiri sesi (HTTP ' . $status . ')');
    [$status, $anonymous] = requestApi('/api/auth.php', 'GET');
    check($status === 200 && array_key_exists('user', $anonymous['data'] ?? []) && $anonymous['data']['user'] === null, 'session baru setelah logout berstatus anonymous');
    fwrite(STDOUT, "{$checks} pemeriksaan lulus.\n");
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    $failed = true;
} finally {
    if ($testId !== null) requestApi('/api/units.php?id=' . $testId, 'DELETE');
}
if ($failed) exit(1);
