<?php
declare(strict_types=1);

namespace Kost\Services;

use Kost\Core\HttpException;
use Kost\Helpers\Validator;
use Kost\Repositories\UnitRepository;
use PDOException;

final class UnitService
{
    public function __construct(private readonly UnitRepository $units)
    {
    }

    public function list(array $query): array
    {
        $q = $query['q'] ?? '';
        if (!is_string($q)) throw new HttpException(400, 'INVALID_PARAMETER', 'Parameter q harus berupa teks');
        $status = $query['status'] ?? '';
        if (!is_string($status)) throw new HttpException(400, 'INVALID_PARAMETER', 'Parameter status harus berupa teks');
        $page = $this->positiveInteger($query['page'] ?? 1, 'page', 1);
        $perPage = $this->positiveInteger($query['per_page'] ?? 10, 'per_page', 1, 50);
        return $this->units->list(trim($q), $status, $page, $perPage);
    }

    public function find(mixed $rawId): array
    {
        return $this->units->find($this->parseId($rawId));
    }

    public function create(array $body): array
    {
        $data = Validator::unit($body);
        try {
            return $this->units->find($this->units->create($data));
        } catch (PDOException $error) {
            $this->rethrowDatabaseConflict($error);
        }
    }

    public function update(mixed $rawId, array $body, bool $partial): array
    {
        $id = $this->parseId($rawId);
        $data = Validator::unit($body, $partial);
        $this->units->find($id);
        try {
            $this->units->update($id, $data);
            return $this->units->find($id);
        } catch (PDOException $error) {
            $this->rethrowDatabaseConflict($error);
        }
    }

    public function delete(mixed $rawId): void
    {
        $id = $this->parseId($rawId);
        $this->units->find($id);
        try {
            $this->units->delete($id);
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) === 1451) {
                throw new HttpException(409, 'IN_USE', 'Unit tidak dapat dihapus karena masih memiliki transaksi sewa');
            }
            throw $error;
        }
    }

    private function parseId(mixed $raw): int
    {
        $id = filter_var($raw, FILTER_VALIDATE_INT);
        if ($id === false || $id <= 0) throw new HttpException(400, 'INVALID_ID', 'ID unit harus berupa bilangan bulat positif');
        return $id;
    }

    private function positiveInteger(mixed $raw, string $name, int $min, int $max = PHP_INT_MAX): int
    {
        $value = filter_var($raw, FILTER_VALIDATE_INT);
        if ($value === false || $value < $min || $value > $max) {
            throw new HttpException(400, 'INVALID_PARAMETER', "{$name} harus bilangan bulat {$min} - {$max}");
        }
        return $value;
    }

    private function rethrowDatabaseConflict(PDOException $error): never
    {
        if ($this->units->isDuplicate($error)) {
            throw new HttpException(409, 'DUPLICATE', 'Kode unit sudah digunakan', ['kode' => 'Kode sudah terdaftar']);
        }
        throw $error;
    }
}
