<?php
$db = db();
$stats = [
    'units' => (int)$db->query('SELECT COUNT(*) FROM unit_kost')->fetchColumn(),
    'available' => (int)$db->query("SELECT COUNT(*) FROM unit_kost WHERE status='Tersedia'")->fetchColumn(),
    'tenants' => (int)$db->query('SELECT COUNT(*) FROM penghuni_kost')->fetchColumn(),
    'active_leases' => (int)$db->query("SELECT COUNT(*) FROM transaksi_sewa WHERE status='Aktif'")->fetchColumn(),
    'open_payments' => (int)$db->query("SELECT COUNT(*) FROM pembayaran_kost WHERE status='Belum Lunas'")->fetchColumn(),
];
?>
<main id="main-content" class="dashboard-page">
  <h1>Dashboard Admin</h1>
  <p>Ringkasan dihitung langsung dari database.</p>
  <section class="stat-grid" aria-label="Ringkasan kost">
    <article><h2><?= $stats['units'] ?></h2><p>Total unit</p></article>
    <article><h2><?= $stats['available'] ?></h2><p>Unit tersedia</p></article>
    <article><h2><?= $stats['tenants'] ?></h2><p>Penghuni</p></article>
    <article><h2><?= $stats['active_leases'] ?></h2><p>Sewa aktif</p></article>
    <article><h2><?= $stats['open_payments'] ?></h2><p>Pembayaran belum lunas</p></article>
  </section>
</main>
