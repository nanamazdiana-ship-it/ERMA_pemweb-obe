<?php
declare(strict_types=1);

namespace Kost\Repositories;

use Kost\Core\HttpException;
use PDO;

final class ComplaintRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function listForTenant(int $tenantId): array
    {
        $statement = $this->db->prepare(
            'SELECT id,judul,deskripsi,status,created_at,updated_at FROM pengaduan_kost
             WHERE penghuni_id=:id ORDER BY created_at DESC,id DESC'
        );
        $statement->execute(['id' => $tenantId]);
        return $statement->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT p.id,p.penghuni_id,h.nama_lengkap,p.judul,p.deskripsi,p.status,p.created_at,p.updated_at
             FROM pengaduan_kost p INNER JOIN penghuni_kost h ON h.id=p.penghuni_id
             ORDER BY p.created_at DESC,p.id DESC'
        )->fetchAll();
    }

    public function create(int $tenantId, string $title, string $description): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO pengaduan_kost (penghuni_id,judul,deskripsi) VALUES (:tenant,:title,:description)'
        );
        $statement->execute(['tenant' => $tenantId, 'title' => $title, 'description' => $description]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): array
    {
        $statement = $this->db->prepare('UPDATE pengaduan_kost SET status=:status WHERE id=:id');
        $statement->execute(['status' => $status, 'id' => $id]);
        $read = $this->db->prepare(
            'SELECT p.id,p.penghuni_id,h.nama_lengkap,p.judul,p.deskripsi,p.status,p.created_at,p.updated_at
             FROM pengaduan_kost p INNER JOIN penghuni_kost h ON h.id=p.penghuni_id WHERE p.id=:id'
        );
        $read->execute(['id' => $id]);
        $complaint = $read->fetch();
        if (!$complaint) throw new HttpException(404, 'NOT_FOUND', 'Pengaduan tidak ditemukan');
        return $complaint;
    }
}
