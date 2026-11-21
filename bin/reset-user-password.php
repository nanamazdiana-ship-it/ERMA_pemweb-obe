<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

if ($argc !== 2 || !filter_var($argv[1] ?? '', FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Penggunaan: php bin/reset-user-password.php <email>\n");
    exit(2);
}

function readNewPassword(): string
{
    if (PHP_OS_FAMILY === 'Windows' && function_exists('proc_open')) {
        $script = '$s=Read-Host "Password baru" -AsSecureString;'
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

    fwrite(STDOUT, 'Password baru: ');
    if (PHP_OS_FAMILY !== 'Windows' && function_exists('stream_isatty') && stream_isatty(STDIN)) {
        shell_exec('stty -echo');
        try {
            $password = rtrim((string)fgets(STDIN), "\r\n");
        } finally {
            shell_exec('stty echo');
            fwrite(STDOUT, "\n");
        }
        return $password;
    }

    fwrite(STDOUT, "(Input pada terminal ini mungkin terlihat.)\n");
    return rtrim((string)fgets(STDIN), "\r\n");
}

try {
    $password = readNewPassword();
    if (strlen($password) < 12) {
        fwrite(STDERR, "Password harus minimal 12 karakter.\n");
        exit(2);
    }

    $statement = db()->prepare('UPDATE pengguna SET password_hash=:hash WHERE email=:email');
    $statement->execute([
        'hash' => password_hash($password, PASSWORD_DEFAULT),
        'email' => mb_strtolower($argv[1]),
    ]);
    if ($statement->rowCount() === 0) {
        $check = db()->prepare('SELECT id FROM pengguna WHERE email=:email');
        $check->execute(['email' => mb_strtolower($argv[1])]);
        if (!$check->fetchColumn()) {
            fwrite(STDERR, "Akun dengan email tersebut tidak ditemukan.\n");
            exit(1);
        }
    }
    fwrite(STDOUT, "Password akun berhasil direset.\n");
} catch (Throwable $error) {
    error_log('[RESET USER PASSWORD] ' . $error->getMessage());
    fwrite(STDERR, "Password gagal direset; periksa konfigurasi database.\n");
    exit(1);
}
