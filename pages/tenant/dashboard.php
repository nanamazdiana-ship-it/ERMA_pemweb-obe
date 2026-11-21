<main id="main-content" class="dashboard-page">
  <h1>Halo, <?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') ?></h1>
  <p>Gunakan halaman ini untuk melihat informasi sewa, pembayaran, dan pengaduan Anda.</p>
  <ul class="tenant-links">
    <li><a href="index.php?page=tenant/my-room">Lihat sewa saya</a></li>
    <li><a href="index.php?page=tenant/payments">Lihat pembayaran</a></li>
    <li><a href="index.php?page=tenant/complaints">Buat atau lihat pengaduan</a></li>
    <li><a href="index.php?page=tenant/profile">Profil akun</a></li>
  </ul>
</main>
