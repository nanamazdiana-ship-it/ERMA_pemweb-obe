<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

if ($argc < 4 || $argc > 5) {
    fwrite(STDERR, "Penggunaan: php bin/create-user.php <nama> <email> <penghuni|petugas|admin> [penghuni_id]\n");
    exit(2);
}

[, $name, $email, $role] = $argv;
$tenantId = isset($argv[4]) && ctype_digit($argv[4]) ? (int)$argv[4] : null;
$validRoles = ['penghuni', 'petugas', 'admin'];
if (trim($name) === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !in_array($role, $validRoles, true)
    || ($role === 'penghuni' && $tenantId === null)
    || ($role !== 'penghuni' && $tenantId !== null)) {
    fwrite(STDERR, "Nama, email, atau role tidak valid.\n");
    exit(2);
}

function readPassword(): string
{
    if (PHP_OS_FAMILY === 'Windows' && function_exists('proc_open')) {
        $script = '$s=Read-Host "Password akun" -AsSecureString;'
            . '$p=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s);'
            . 'try{[Console]::Out.Write([Runtime.InteropServices.Marshal]::PtrToStringBSTR($p))}'
            . 'finally{[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($p)}';
        $pipes = [];
        $process = proc_open(
            ['powershell.exe', '-NoLogo', '-NoProfile', '-Command', $script],
            [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR],
            $pipes
        );
        if (!is_resource($process)) throw new RuntimeException('Tidak dapat membuka prompt password aman.');
        $password = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);
        fwrite(STDOUT, "\n");
        if ($exitCode !== 0 || $password === false) throw new RuntimeException('Gagal membaca password.');
        return rtrim($password, "\r\n");
    }

    fwrite(STDOUT, 'Password: ');
    if (PHP_OS_FAMILY !== 'Windows' && function_exists('stream_isatty') && stream_isatty(STDIN)) {
        shell_exec('stty -echo');
        try {
            $password = trim((string)fgets(STDIN));
        } finally {
            shell_exec('stty echo');
            fwrite(STDOUT, "\n");
        }
        return $password;
    }

    fwrite(STDOUT, "(Input pada terminal ini mungkin terlihat.)\n");
    return trim((string)fgets(STDIN));
}

try {
    $password = readPassword();
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
if (strlen($password) < 12) {
    fwrite(STDERR, "Password harus minimal 12 karakter.\n");
    exit(2);
}

try {
    if ($tenantId !== null) {
        $tenantCheck = db()->prepare('SELECT id FROM penghuni_kost WHERE id = :id');
        $tenantCheck->execute(['id' => $tenantId]);
        if (!$tenantCheck->fetchColumn()) {
            fwrite(STDERR, "ID penghuni tidak ditemukan.\n");
            exit(2);
        }
    }
    $stmt = db()->prepare(
        'INSERT INTO pengguna (nama, email, password_hash, role, penghuni_id) VALUES (:nama, :email, :hash, :role, :penghuni_id)'
    );
    $stmt->execute([
        'nama' => trim($name),
        'email' => mb_strtolower($email),
        'hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
        'penghuni_id' => $tenantId,
    ]);
    fwrite(STDOUT, "Akun berhasil dibuat.\n");
} catch (PDOException $e) {
    if (($e->errorInfo[1] ?? null) === 1062) {
        fwrite(STDERR, "Email tersebut sudah terdaftar.\n");
        exit(1);
    }
    error_log('[CREATE USER] ' . $e->getMessage());
    fwrite(STDERR, "Akun gagal dibuat; periksa konfigurasi database.\n");
    exit(1);
}
