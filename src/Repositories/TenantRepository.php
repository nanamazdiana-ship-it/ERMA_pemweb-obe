<?php
declare(strict_types=1);

namespace Kost\Repositories;

use Kost\Core\HttpException;
use PDO;

final class TenantRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function list(): array
    {
        return $this->db->query('SELECT id,nama_lengkap,no_hp,alamat,created_at FROM penghuni_kost ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): array
    {
        $statement = $this->db->prepare('SELECT id,nama_lengkap,no_hp,alamat,created_at FROM penghuni_kost WHERE id=:id');
        $statement->execute(['id' => $id]);
        $tenant = $statement->fetch();
        if (!$tenant) throw new HttpException(404, 'NOT_FOUND', 'Data penghuni tidak ditemukan');
        return $tenant;
    }
}
