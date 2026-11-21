<main id="main-content">
  <h1>Pengaduan Saya</h1>
  <p id="complaints-message" class="status-message" role="status" aria-live="polite"></p>
  <form id="complaint-form" novalidate>
    <p><label for="complaint-title">Judul</label><input id="complaint-title" maxlength="150" required></p>
    <p><label for="complaint-description">Deskripsi</label><textarea id="complaint-description" rows="5" maxlength="5000" required></textarea></p>
    <button type="submit">Kirim pengaduan</button>
  </form>
  <section id="complaints-list" class="unit-grid" aria-live="polite"></section>
</main>
