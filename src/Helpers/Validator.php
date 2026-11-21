<?php
declare(strict_types=1);

namespace Kost\Helpers;

use Kost\Core\HttpException;

final class Validator
{
    private const UNIT_STATUSES = ['Tersedia', 'Terisi'];

    public static function unit(array $input, bool $partial = false): array
    {
        $errors = [];
        $clean = [];
        $allowed = ['kode', 'nama', 'status', 'harga', 'fasilitas'];
        foreach (array_diff(array_keys($input), $allowed) as $unknown) {
            $errors[(string)$unknown] = 'Field tidak dikenal';
        }

        if (!$partial || array_key_exists('kode', $input)) {
            $value = is_string($input['kode'] ?? null) ? trim($input['kode']) : '';
            if ($value === '' || mb_strlen($value) > 20) $errors['kode'] = 'Kode wajib diisi (maksimal 20 karakter)';
            else $clean['kode'] = $value;
        }
        if (!$partial || array_key_exists('nama', $input)) {
            $value = is_string($input['nama'] ?? null) ? trim($input['nama']) : '';
            if ($value === '' || mb_strlen($value) > 100) $errors['nama'] = 'Nama wajib diisi (maksimal 100 karakter)';
            else $clean['nama'] = $value;
        }
        if (!$partial || array_key_exists('status', $input)) {
            $value = $input['status'] ?? null;
            if (!is_string($value) || !in_array($value, self::UNIT_STATUSES, true)) $errors['status'] = 'Status harus Tersedia atau Terisi';
            else $clean['status'] = $value;
        }
        if (!$partial || array_key_exists('harga', $input)) {
            $value = $input['harga'] ?? null;
            $value = is_bool($value) || is_array($value) ? false : filter_var($value, FILTER_VALIDATE_INT);
            if ($value === false || $value < 0 || $value > 100000000) $errors['harga'] = 'Harga harus bilangan bulat 0 - 100.000.000';
            else $clean['harga'] = $value;
        }
        if (!$partial || array_key_exists('fasilitas', $input)) {
            $value = $input['fasilitas'] ?? '';
            if (!is_string($value) || mb_strlen($value) > 1000) $errors['fasilitas'] = 'Fasilitas harus berupa teks maksimal 1.000 karakter';
            else $clean['fasilitas'] = trim($value);
        }

        if ($errors) throw new HttpException(422, 'VALIDATION_ERROR', 'Data unit tidak valid', $errors);
        if ($partial && $clean === []) throw new HttpException(422, 'VALIDATION_ERROR', 'Tidak ada field yang diubah', ['body' => 'Kirim minimal satu field']);
        return $clean;
    }
}
