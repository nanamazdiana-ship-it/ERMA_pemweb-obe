<?php
declare(strict_types=1);

namespace Kost\Repositories;

use PDO;

final class PaymentRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function forTenant(int $tenantId): array
    {
        $statement = $this->db->prepare(
            'SELECT id,jumlah_bayar,tanggal_bayar,status,keterangan,created_at
             FROM pembayaran_kost WHERE penghuni_id=:id ORDER BY tanggal_bayar DESC,id DESC'
        );
        $statement->execute(['id' => $tenantId]);
        return $statement->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT p.id,p.penghuni_id,h.nama_lengkap,p.jumlah_bayar,p.tanggal_bayar,p.status,p.keterangan,p.created_at
             FROM pembayaran_kost p INNER JOIN penghuni_kost h ON h.id=p.penghuni_id
             ORDER BY p.tanggal_bayar DESC,p.id DESC'
        )->fetchAll();
    }
}
