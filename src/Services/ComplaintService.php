<?php
declare(strict_types=1);

namespace Kost\Services;

use Kost\Core\HttpException;
use Kost\Repositories\ComplaintRepository;

final class ComplaintService
{
    public function __construct(private readonly ComplaintRepository $complaints)
    {
    }

    public function create(array $user, array $body): array
    {
        $title = $body['judul'] ?? null;
        $description = $body['deskripsi'] ?? null;
        $errors = [];
        if (!is_string($title) || trim($title) === '' || mb_strlen(trim($title)) > 150) {
            $errors['judul'] = 'Judul wajib diisi (maksimal 150 karakter)';
        }
        if (!is_string($description) || trim($description) === '' || mb_strlen(trim($description)) > 5000) {
            $errors['deskripsi'] = 'Deskripsi wajib diisi (maksimal 5.000 karakter)';
        }
        foreach (array_diff(array_keys($body), ['judul', 'deskripsi']) as $unknown) {
            $errors[(string)$unknown] = 'Field tidak dikenal';
        }
        if ($errors) throw new HttpException(422, 'VALIDATION_ERROR', 'Data pengaduan tidak valid', $errors);
        if (empty($user['penghuni_id'])) throw new HttpException(409, 'TENANT_ACCOUNT_UNLINKED', 'Akun belum ditautkan ke data penghuni');
        $id = $this->complaints->create((int)$user['penghuni_id'], trim($title), trim($description));
        return ['id' => $id, 'status' => 'Baru'];
    }

    public function updateStatus(mixed $rawId, array $body): array
    {
        $id = filter_var($rawId, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) throw new HttpException(400, 'INVALID_ID', 'ID pengaduan harus positif');
        $status = $body['status'] ?? null;
        if (!is_string($status) || !in_array($status, ['Baru', 'Diproses', 'Selesai'], true)) {
            throw new HttpException(422, 'VALIDATION_ERROR', 'Status pengaduan tidak valid', ['status' => 'Pilih Baru, Diproses, atau Selesai']);
        }
        foreach (array_diff(array_keys($body), ['status']) as $unknown) {
            throw new HttpException(422, 'VALIDATION_ERROR', 'Field tidak dikenal', [(string)$unknown => 'Field tidak dikenal']);
        }
        return $this->complaints->updateStatus($id, $status);
    }
}
