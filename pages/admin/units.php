<main id="main-content">
<!-- ==========================================
         4.2b KELOLA UNIT (CRUD FULL-STACK)
         Modul 11: UI -> API (api/units.php) -> MySQL
         ========================================== -->
    <section id="kelola-unit" class="crud-section">

      <h2>Kelola Unit Kost (CRUD)</h2>

      <!-- Pesan sukses/gagal operasi -->
      <p id="crud-message" class="status-message" role="status" aria-live="polite"></p>

      <!-- Catatan hak akses (diisi JavaScript sesuai role) -->
      <p id="crud-readonly-note" class="status-message" hidden></p>

      <!-- Form tambah / ubah unit -->
      <form id="crud-form" novalidate>
        <h3 id="crud-form-title">Tambah Unit Baru</h3>

        <input type="hidden" id="crud-id">

        <p>
          <label for="crud-kode">Kode Pintu:</label>
          <input type="text" id="crud-kode" maxlength="20"
                 placeholder="Contoh: Pintu-04" required
                 aria-describedby="crud-kode-error">
          <span id="crud-kode-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <label for="crud-nama">Nama Unit:</label>
          <input type="text" id="crud-nama" maxlength="100"
                 placeholder="Contoh: Pintu 4" required
                 aria-describedby="crud-nama-error">
          <span id="crud-nama-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <label for="crud-status">Status:</label>
          <select id="crud-status" required aria-describedby="crud-status-error">
            <option value="Tersedia">Tersedia</option>
            <option value="Terisi">Terisi</option>
          </select>
          <span id="crud-status-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <label for="crud-harga">Harga Sewa per Bulan (Rp):</label>
          <input type="number" id="crud-harga" min="0" max="100000000" step="1"
                 placeholder="Contoh: 1000000" required
                 aria-describedby="crud-harga-error">
          <span id="crud-harga-error" class="error-text" aria-live="polite"></span>
        </p>

        <p>
          <label for="crud-fasilitas">Fasilitas:</label>
          <textarea id="crud-fasilitas" maxlength="1000" rows="3"
                    aria-describedby="crud-fasilitas-error"></textarea>
          <span id="crud-fasilitas-error" class="error-text" aria-live="polite"></span>
        </p>

        <p class="crud-actions">
          <button type="submit" id="crud-submit">Simpan Unit</button>
          <button type="button" id="crud-cancel" hidden>Batal Ubah</button>
        </p>
      </form>

      <!-- Pencarian, filter status, pagination -->
      <p class="toolbar" aria-label="Pencarian dan filter unit">
        <label for="crud-cari">Cari (kode/nama):</label>
        <input type="search" id="crud-cari" placeholder="Ketik kata kunci...">

        <label for="crud-filter-status">Status:</label>
        <select id="crud-filter-status">
          <option value="">Semua</option>
          <option value="Tersedia">Tersedia</option>
          <option value="Terisi">Terisi</option>
        </select>

        <label for="crud-per-page">Per halaman:</label>
        <select id="crud-per-page">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="20">20</option>
        </select>
      </p>

      <table id="crud-table">
        <caption>Daftar unit kost dari database</caption>
        <thead>
          <tr>
            <th scope="col">Kode</th>
            <th scope="col">Nama</th>
            <th scope="col">Status</th>
            <th scope="col">Harga/bulan</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody id="crud-tbody"></tbody>
      </table>

      <nav class="pagination" aria-label="Halaman daftar unit">
        <button type="button" id="crud-prev">Sebelumnya</button>
        <span id="crud-page-info" aria-live="polite"></span>
        <button type="button" id="crud-next">Berikutnya</button>
      </nav>

    </section>
    <!-- AKHIR KELOLA UNIT -->
</main>
