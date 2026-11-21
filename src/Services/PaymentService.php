<?php
declare(strict_types=1);

namespace Kost\Services;

use Kost\Core\Db;
use Kost\Repositories\PaymentRepository;

final class PaymentService
{
    public function __construct(private readonly PaymentRepository $payments)
    {
    }

    public function listFor(array $user): array
    {
        if ($user['role'] === 'penghuni') {
            return empty($user['penghuni_id']) ? [] : $this->payments->forTenant((int)$user['penghuni_id']);
        }
        return $this->payments->all();
    }

    public static function leaseAndPaymentSummary(array $user): array
    {
        if ($user['role'] !== 'penghuni' || empty($user['penghuni_id'])) {
            return ['leases' => [], 'payments' => []];
        }
        $id = (int)$user['penghuni_id'];
        $db = Db::connection();
        $leases = $db->prepare(
            'SELECT t.id,u.kode_pintu,u.nama_pintu,t.tanggal_mulai,t.tanggal_selesai,t.harga_sewa,t.status
             FROM transaksi_sewa t INNER JOIN unit_kost u ON u.id=t.unit_id
             WHERE t.penghuni_id=:id ORDER BY t.tanggal_mulai DESC,t.id DESC'
        );
        $leases->execute(['id' => $id]);
        $payments = (new PaymentRepository($db))->forTenant($id);
        return ['leases' => $leases->fetchAll(), 'payments' => $payments];
    }
}
