<?php
$user = $user ?? null;
?>
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>
<header>
  <a href="index.php" aria-label="Beranda Kost Pak Sapriadi">
    <img src="assets/images/logo.png" alt="Logo Kost Pak Sapriadi" width="120" height="auto">
  </a>
  <nav aria-label="Navigasi utama">
    <ul>
      <li><a href="index.php">Beranda</a></li>
      <li><a href="index.php?page=public/units">Katalog unit</a></li>
      <?php if ($user && in_array($user['role'], ['admin', 'petugas'], true)): ?>
        <li><a href="index.php?page=admin/dashboard">Dashboard</a></li>
        <li><a href="index.php?page=admin/units">Kelola unit</a></li>
        <li><a href="index.php?page=admin/tenants">Penghuni</a></li>
        <li><a href="index.php?page=admin/payments">Pembayaran</a></li>
        <li><a href="index.php?page=admin/complaints">Pengaduan</a></li>
      <?php elseif ($user && $user['role'] === 'penghuni'): ?>
        <li><a href="index.php?page=tenant/dashboard">Dashboard saya</a></li>
        <li><a href="index.php?page=tenant/my-room">Sewa saya</a></li>
        <li><a href="index.php?page=tenant/payments">Pembayaran</a></li>
        <li><a href="index.php?page=tenant/complaints">Pengaduan</a></li>
        <li><a href="index.php?page=tenant/profile">Profil</a></li>
      <?php endif; ?>
      <li><a class="login-link" href="index.php?page=auth/login"><?= $user ? 'Akun' : 'Login' ?></a></li>
      <li><button id="theme-button" class="theme-button" type="button" aria-label="Ganti ke tema gelap" title="Ganti ke tema gelap" aria-pressed="false"></button></li>
    </ul>
  </nav>
</header>
