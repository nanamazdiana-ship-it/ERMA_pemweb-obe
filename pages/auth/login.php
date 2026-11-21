<main id="main-content">
<!-- ==========================================
         4.2a AKUN (LOGIN / LOGOUT) - Modul 12
         UI hanya kenyamanan; otorisasi sebenarnya di server.
         ========================================== -->
    <section id="akun" class="auth-section">

      <h2>Akun</h2>

      <p id="auth-message" class="status-message" role="status" aria-live="polite"></p>

      <!-- Form login: tampil bila belum masuk -->
      <form id="login-form" novalidate>
        <h3>Masuk</h3>

        <p>
          <label for="login-email">Email:</label>
          <input type="email" id="login-email" autocomplete="username"
                 placeholder="Contoh: penghuni@kost.test" required
                 aria-describedby="login-email-error">
          <span id="login-email-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <label for="login-password">Password:</label>
          <input type="password" id="login-password" autocomplete="current-password"
                 required aria-describedby="login-password-error">
          <span id="login-password-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <button type="submit" id="login-submit">Masuk</button>
        </p>
      </form>

      <!-- Profil: tampil bila sudah masuk -->
      <article id="profil" hidden>
        <h3>Profil Saya</h3>
        <p>Masuk sebagai <strong id="profil-nama"></strong> (<span id="profil-email"></span>)</p>
        <p>Hak akses: <strong id="profil-role"></strong></p>

        <!-- Khusus penghuni: sewa milik sendiri -->
        <section id="sewa-saya" hidden>
          <h4>Sewa &amp; Pembayaran Saya</h4>
          <ul id="sewa-list"></ul>
        </section>

        <p>
          <button type="button" id="logout-btn">Keluar</button>
        </p>
      </article>

    </section>
    <!-- AKHIR AKUN -->
</main>
