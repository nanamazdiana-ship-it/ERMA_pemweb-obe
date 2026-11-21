<?php
declare(strict_types=1);

namespace Kost\Repositories;

use PDO;

final class FacilityRepository
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function all(): array
    {
        $rows = $this->db->query('SELECT id,nama,kategori,jumlah,kondisi,lokasi,updated_at FROM inventaris_kost ORDER BY nama')->fetchAll();
        foreach ($rows as &$row) {
            $row['id'] = (int)$row['id'];
            $row['jumlah'] = (int)$row['jumlah'];
        }
        unset($row);
        return $rows;
    }
}
