<?php
declare(strict_types=1);

namespace Kost\Repositories;

use Kost\Core\HttpException;
use PDO;
use PDOException;

final class UnitRepository
{
    private const SELECT = 'SELECT id, kode_pintu AS kode, nama_pintu AS nama, status, harga_sewa AS harga, fasilitas FROM unit_kost';

    public function __construct(private readonly PDO $db)
    {
    }

    public function list(string $query, string $status, int $page, int $perPage): array
    {
        $where = [];
        $params = [];
        if ($status !== '') {
            if (!in_array($status, ['Tersedia', 'Terisi'], true)) {
                throw new HttpException(400, 'INVALID_PARAMETER', 'Status harus Tersedia atau Terisi');
            }
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($query !== '') {
            $like = '%' . addcslashes($query, '\\%_') . '%';
            $where[] = '(kode_pintu LIKE :q1 OR nama_pintu LIKE :q2 OR fasilitas LIKE :q3)';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $count = $this->db->prepare('SELECT COUNT(*) FROM unit_kost' . $whereSql);
        $count->execute($params);
        $total = (int)$count->fetchColumn();

        $statement = $this->db->prepare(self::SELECT . $whereSql . ' ORDER BY id ASC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) $statement->bindValue(':' . $key, $value);
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();
        $units = array_map([$this, 'normalize'], $statement->fetchAll());
        return ['data' => $units, 'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => max(1, (int)ceil($total / $perPage)),
        ]];
    }

    public function find(int $id): array
    {
        $statement = $this->db->prepare(self::SELECT . ' WHERE id = :id');
        $statement->execute(['id' => $id]);
        $unit = $statement->fetch();
        if (!$unit) throw new HttpException(404, 'NOT_FOUND', 'Data unit kost tidak ditemukan');
        return $this->normalize($unit);
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO unit_kost (kode_pintu, nama_pintu, status, harga_sewa, fasilitas)
             VALUES (:kode, :nama, :status, :harga, :fasilitas)'
        );
        $statement->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $map = ['kode' => 'kode_pintu', 'nama' => 'nama_pintu', 'status' => 'status', 'harga' => 'harga_sewa', 'fasilitas' => 'fasilitas'];
        $sets = [];
        foreach (array_keys($data) as $field) $sets[] = $map[$field] . ' = :' . $field;
        $statement = $this->db->prepare('UPDATE unit_kost SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $statement->execute($data + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM unit_kost WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function isDuplicate(PDOException $error): bool
    {
        return ($error->errorInfo[1] ?? null) === 1062;
    }

    private function normalize(array $unit): array
    {
        $unit['id'] = (int)$unit['id'];
        $unit['harga'] = (int)$unit['harga'];
        $unit['fasilitas'] = $unit['fasilitas'] ?? '';
        return $unit;
    }
}
