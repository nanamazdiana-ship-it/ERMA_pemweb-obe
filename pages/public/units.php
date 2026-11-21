<main id="main-content">
<section id="katalog">

      <h2>Daftar Ketersediaan Unit</h2>

      <!-- Indikator status loading dan error API -->
      <p
        id="api-message"
        class="status-message"
        aria-live="polite"
      ></p>

      <!-- Tombol mencoba kembali saat data gagal dimuat -->
      <p>
        <button
          id="btn-retry"
          type="button"
          style="display: none;"
        >
          Coba Lagi (Retry)
        </button>
      </p>

      <!-- Toolbar pencarian katalog unit -->
      <p class="toolbar" aria-label="Filter dan Pencarian Unit">

        <label for="searchInput">
          Cari Unit / Fasilitas:
        </label>

        <input
          type="search"
          id="searchInput"
          placeholder="Ketik kata kunci unit (misal: Pintu 01 / Wi-Fi)..."
        >

      </p>

      <!-- Wadah target DOM untuk kartu unit dari JavaScript -->
      <section
        id="api-output"
        class="unit-grid"
        aria-live="polite"
      ></section>

    </section>
    <!-- AKHIR KATALOG UNIT KOST -->
</main>
