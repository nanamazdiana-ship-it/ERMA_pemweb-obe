<main id="main-content">

    <!-- ==========================================
         4.1 PROFIL KOST
         Informasi singkat mengenai Kost Pak Sapriadi
         ========================================== -->
    <section id="tentang" class="intro-card">

      <h1>Kost Pak Sapriadi</h1>

      <p>
        Layanan hunian sewa 3 pintu yang tenang dan strategis
        dekat kampus. Setiap unit memiliki ruang tengah,
        2 kamar tidur di dalamnya, dapur, dan toilet dengan
        tarif Rp 1.000.000 per bulan.
      </p>

    </section>
    <!-- AKHIR PROFIL KOST -->


    <!-- ==========================================
         4.2 KATALOG UNIT KOST
         Modul 7: Data dinamis dari API / JSON
         ========================================== -->
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

    

    


    <!-- =====================================
     ROOM TOUR 360 DERAJAT
====================================== -->
<section id="room-tour" class="room-tour-section">
  <hgroup class="room-tour-heading">
    <h2>Jelajahi Kost Kami</h2>
    <p>
      Lihat area kost secara interaktif melalui Room Tour 360Â°.
      Pilih ruangan dan geser gambar untuk melihat sekeliling.
    </p>
  </hgroup>

  <!-- Pilihan ruangan -->
  <p class="room-tour-buttons" role="group" aria-label="Pilih ruangan">
    <button type="button" data-scene="pintu">
      Pintu Masuk
    </button>

    <button type="button" data-scene="tengah">
      Ruang Tengah
    </button>

    <button type="button" data-scene="kamar1">
      Kamar 1
    </button>

    <button type="button" data-scene="kamar2">
      Kamar 2
    </button>

    <button type="button" data-scene="dapur">
      Dapur
    </button>
  </p>

  <!-- Tampilan panorama 360 derajat -->
  <section class="room-tour-viewer" aria-label="Room tour 360 derajat">
    <figure
      id="panorama"
      role="region"
      aria-label="Penampil panorama 360 derajat"
    ></figure>
  </section>

  <p id="room-tour-caption" class="room-tour-caption" aria-live="polite">
    Memuat panorama contoh...
  </p>

  <p class="room-tour-note">
    <strong>Catatan:</strong> Tampilan saat ini menggunakan panorama
    demonstrasi, bukan foto asli ruangan kost.
  </p>
</section>



    <!-- ==========================================
         4.4 INVENTARIS KOST
         Pencarian, filter kondisi, dan pagination
         ========================================== -->
    <section id="inventaris">

      <h2>Inventaris Kost</h2>
      <p id="inventory-message" class="status-message" role="status" aria-live="polite"></p>

      <!-- Pencarian nama inventaris -->
      <p class="search-toolbar">

        <label for="cari-inventaris">
          Cari nama inventaris:
        </label>

        <input
          type="search"
          id="cari-inventaris"
          placeholder="Contoh: Router Wi-Fi"
          autocomplete="off"
        >

      </p>

      <!-- Toolbar filter kondisi inventaris -->
      <p
        class="toolbar"
        role="group"
        aria-label="Filter inventaris kost"
      >

        <span>Filter Kondisi:</span>

        <button data-filter="Semua" type="button">
          Semua
        </button>

        <button data-filter="Baik" type="button">
          Baik
        </button>

        <button data-filter="Perlu Cek" type="button">
          Perlu Cek
        </button>

      </p>

      <!-- Pengaturan jumlah item per halaman -->
      <p class="page-size-control">

        <label for="jumlah-per-halaman">
          Jumlah item per halaman:
        </label>

        <select id="jumlah-per-halaman">
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="20">20</option>
        </select>

      </p>

      <!-- Kartu inventaris ditampilkan oleh JavaScript -->
      <section
        id="daftar-alat"
        aria-live="polite"
      ></section>

      <!-- Navigasi halaman inventaris -->
      <nav
        id="pagination-controls"
        class="pagination"
        aria-label="Navigasi halaman inventaris"
      ></nav>

    </section>
    <!-- AKHIR INVENTARIS KOST -->


    <!-- ==========================================
         4.5 FORMULIR DATA MINAT
         Modul validasi formulir dan pratinjau data
         ========================================== -->
    <section id="kontak">

      <h2>Formulir Data Minat</h2>

      <form id="form-minat" novalidate>

        <!-- Input nama lengkap -->
        <p>
          <label for="nama">Nama Lengkap:</label>

          <input
            type="text"
            id="nama"
            name="nama"
            required
            minlength="3"
            pattern="[\p{L} ]{3,}"
            aria-describedby="nama-error"
            placeholder="Masukkan nama Anda"
            autocomplete="name"
          >

          <small
            id="nama-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Input nomor WhatsApp -->
        <p>
          <label for="telepon">Nomor WhatsApp:</label>

          <input
            type="tel"
            id="telepon"
            name="telepon"
            required
            inputmode="numeric"
            aria-describedby="telepon-error"
            placeholder="08xxxxxxxxxx"
            autocomplete="tel"
          >

          <small
            id="telepon-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Input alamat email -->
        <p>
          <label for="email">Email:</label>

          <input
            type="email"
            id="email"
            name="email"
            required
            aria-describedby="email-error"
            placeholder="nama@contoh.com"
            autocomplete="email"
          >

          <small
            id="email-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Pilihan unit pintu -->
        <p>
          <label for="pilihan-pintu">Pilih Unit Pintu:</label>

          <select
            id="pilihan-pintu"
            name="pilihan_pintu"
            required
            aria-describedby="pilihan-pintu-error"
          >
            <option value="">-- Pilih Unit Pintu --</option>
          </select>

          <small
            id="pilihan-pintu-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Tanggal rencana survei (opsional) -->
        <p>
          <label for="tanggal-survei">
            Tanggal Rencana Survei (opsional):
          </label>

          <input
            type="date"
            id="tanggal-survei"
            name="tanggal_survei"
            aria-describedby="tanggal-survei-error"
          >

          <small
            id="tanggal-survei-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Pesan atau rencana survei -->
        <p>
          <label for="pesan">Pesan atau Rencana Survei:</label>

          <textarea
            id="pesan"
            name="pesan"
            rows="4"
            aria-describedby="pesan-error"
            placeholder="Tuliskan pertanyaan atau rencana survei..."
          ></textarea>

          <small
            id="pesan-error"
            class="error-text"
            aria-live="polite"
          ></small>
        </p>

        <!-- Tombol pratinjau data minat -->
        <p>
          <button type="submit">
            Pratinjau Data Minat
          </button>
        </p>

      </form>

      <!-- Ringkasan data minat yang diisi pengguna -->
      <section
        id="daftar-minat"
        class="summary-card"
        aria-labelledby="daftar-minat-heading"
        aria-live="polite"
        hidden
      >

        <h3 id="daftar-minat-heading">
          Daftar Isian Data Minat
        </h3>

        <ol id="item-daftar-minat"></ol>

      </section>
      <!-- AKHIR RINGKASAN DATA MINAT -->

    </section>
    <!-- AKHIR FORMULIR DATA MINAT -->

  </main>
