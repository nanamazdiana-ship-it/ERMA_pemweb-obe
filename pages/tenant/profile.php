<main id="main-content">
  <h1>Profil Akun</h1>
  <dl class="profile-list">
    <dt>Nama akun</dt><dd><?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') ?></dd>
    <dt>Email</dt><dd><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></dd>
    <dt>Role</dt><dd><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></dd>
  </dl>
  <button type="button" id="logout-btn">Keluar</button>
  <p id="auth-message" role="status" aria-live="polite"></p>
</main>
